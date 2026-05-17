<?php

namespace App\Http\Services;

use App\Prompts\AiPrompt;
use App\Http\Repositories\AiRepository;
use App\Http\Repositories\AiMessageRepository;
use App\Http\Repositories\ConversationRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AiService
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';
    private const MODEL = 'gpt-4o-mini';

    public function __construct(
        private readonly AiRepository $aiRepository,
        private readonly AiMessageRepository $messageRepository,
        private readonly ConversationRepository $conversationRepository,
    ) {}

    public function analyze(int $userId, string $message): string
    {
        $this->messageRepository->save($userId, 'user', $message);

        $context = $this->aiRepository->getFinancialContext($userId);
        $systemPrompt = AiPrompt::system($context);

        $contextMessages = $this->conversationRepository->getContext($userId);
        $messages = [...$contextMessages, ['role' => 'user', 'content' => $message]];

        $reply = $this->callApi($systemPrompt, $messages);

        $this->messageRepository->save($userId, 'assistant', $reply);
        $this->conversationRepository->append($userId, $message, $reply);

        return $reply;
    }

    public function getHistory(int $userId): Collection
    {
        return $this->messageRepository->getHistory($userId);
    }

    public function clearConversation(int $userId): void
    {
        $this->messageRepository->clearHistory($userId);
        $this->conversationRepository->clear($userId);
    }

    private function callApi(string $systemPrompt, array $messages): string
    {
        $payload = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$messages,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type'  => 'application/json',
        ])
        ->timeout(30)
        ->post(self::API_URL, [
            'model' => self::MODEL,
            'max_tokens' => 1024,
            'messages' => $payload,
        ]);

        if ($response->failed()) {
            throw new \Exception('Erro ao contatar a API da IA: ' . $response->body());
        }

        return $response->json('choices.0.message.content');
    }
}
