<?php

namespace App\Http\Services;

use App\Models\User;
use App\Prompts\ChatbotPrompt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ChatbotConversationService
{
    private const MAX_TOOL_ROUNDS_BEFORE_FORCED_REPLY = 20;
    private const FALLBACK_REPLY = 'Nao consegui processar sua mensagem agora. Tenta de novo em alguns instantes, por favor.';

    public function __construct(
        private readonly ChatbotLlmService $llmService,
        private readonly ChatbotToolService $toolService,
    ) {}

    public function reply(User $user, string $message): string
    {
        try {
            $history = $this->getHistory($user->id);
            $conversation = [
                ['role' => 'system', 'content' => ChatbotPrompt::system(now()->toDateString())],
                ...$history,
                ['role' => 'user', 'content' => $message],
            ];

            $newMessages = [
                ['role' => 'user', 'content' => $message],
            ];

            $assistantMessage = $this->llmService->chat($conversation);
            $rounds = 0;

            while ($this->hasToolCalls($assistantMessage)) {
                $assistantMessage = $this->normalizeAssistantMessage($assistantMessage);
                $conversation[] = $assistantMessage;
                $newMessages[] = $assistantMessage;

                foreach ($assistantMessage['tool_calls'] as $toolCall) {
                    $toolResult = $this->executeToolCall($user, $toolCall);
                    $conversation[] = $toolResult;
                    $newMessages[] = $toolResult;
                }

                $assistantMessage = $this->llmService->chat($conversation);
                $rounds++;

                if ($rounds >= self::MAX_TOOL_ROUNDS_BEFORE_FORCED_REPLY) {
                    Log::warning('Chatbot atingiu o limite de rodadas de tools; forçando resposta final.', [
                        'user_id' => $user->id,
                        'rounds' => $rounds,
                    ]);

                    $conversation[] = [
                        'role' => 'system',
                        'content' => 'Responda ao usuario agora, sem chamar mais ferramentas. Se faltar algum dado, informe isso de forma objetiva.',
                    ];

                    $assistantMessage = $this->llmService->chat($conversation, 'none');
                    break;
                }
            }

            $finalMessage = $this->normalizeAssistantMessage($assistantMessage);
            $conversation[] = $finalMessage;
            $newMessages[] = $finalMessage;

            $this->storeHistory($user->id, [...$history, ...$newMessages]);

            return $finalMessage['content'] ?: self::FALLBACK_REPLY;
        } catch (Throwable $exception) {
            Log::error('Erro ao processar conversa do chatbot.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return self::FALLBACK_REPLY;
        }
    }

    public function clear(int $userId): void
    {
        Redis::connection($this->connection())->del($this->key($userId));
    }

    private function executeToolCall(User $user, array $toolCall): array
    {
        $name = $toolCall['function']['name'] ?? '';
        $arguments = $this->decodeArguments($toolCall['function']['arguments'] ?? '{}');

        try {
            $result = match ($name) {
                'consultar_categorias' => $this->toolService->consultarCategorias($user),
                'registrar_transacao' => $this->toolService->registrarTransacao($user, $arguments),
                'consultar_transacoes' => $this->toolService->consultarTransacoes($user, $arguments),
                default => ['error' => 'Ferramenta desconhecida.'],
            };
        } catch (Throwable $exception) {
            Log::error('Erro ao executar tool do chatbot.', [
                'user_id' => $user->id,
                'tool' => $name,
                'arguments' => $arguments,
                'exception' => $exception,
            ]);

            $result = ['error' => 'Nao foi possivel executar essa acao agora.'];
        }

        return [
            'role' => 'tool',
            'tool_call_id' => $toolCall['id'] ?? '',
            'name' => $name,
            'content' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    private function getHistory(int $userId): array
    {
        $raw = Redis::connection($this->connection())->get($this->key($userId));

        if (!$raw) {
            return [];
        }

        $history = json_decode($raw, true);

        return is_array($history) ? $history : [];
    }

    private function storeHistory(int $userId, array $history): void
    {
        $history = array_values(array_slice($history, -$this->maxMessages()));

        Redis::connection($this->connection())->setex(
            $this->key($userId),
            $this->ttl(),
            json_encode($history, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function normalizeAssistantMessage(array $message): array
    {
        $normalized = [
            'role' => 'assistant',
            'content' => $message['content'] ?? '',
        ];

        if (!empty($message['tool_calls'])) {
            $normalized['tool_calls'] = $message['tool_calls'];
        }

        return $normalized;
    }

    private function hasToolCalls(array $message): bool
    {
        return !empty($message['tool_calls']) && is_array($message['tool_calls']);
    }

    private function decodeArguments(string $arguments): array
    {
        $decoded = json_decode($arguments, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function connection(): string
    {
        return config('services.chatbot.memory.connection', 'default');
    }

    private function key(int $userId): string
    {
        return str_replace(
            '{userId}',
            (string) $userId,
            config('services.chatbot.memory.key_template', 'whatsapp_chatbot:{userId}')
        );
    }

    private function ttl(): int
    {
        return (int) config('services.chatbot.memory.ttl', 172800);
    }

    private function maxMessages(): int
    {
        return (int) config('services.chatbot.memory.max_messages', 40);
    }
}
