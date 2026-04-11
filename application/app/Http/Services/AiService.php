<?php

namespace App\Services;

use App\Prompts\AiPrompt;
use App\Repositories\AiRepository;
use App\Repositories\AiMessageRepository;
use App\Repositories\ConversationRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AiService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL   = 'claude-sonnet-4-20250514';

    public function __construct(
        private readonly AiRepository           $aiRepository,
        private readonly AiMessageRepository    $messageRepository,
        private readonly ConversationRepository $conversationRepository,
    ) {}


    public function analyze(int $userId, string $message): string
    {
        $this->messageRepository->save($userId, 'user', $message);

        $context      = $this->aiRepository->getFinancialContext($userId);
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
        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])
        ->timeout(30)
        ->post(self::API_URL, [
            'model'      => self::MODEL,
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => $messages,
        ]);

        if ($response->failed()) {
            throw new \Exception('Erro ao contatar a API da IA: ' . $response->body());
        }

        return $response->json('content.0.text');
    }
}
