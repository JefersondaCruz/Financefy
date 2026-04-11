<?php

namespace App\Services;

use App\Prompts\AiPrompt;
use App\Repositories\AiRepository;
use App\Repositories\AiMessageRepository;
use App\Repositories\ConversationRepository;
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

    /**
     * Process a new message:
     * 1. Save user message to DB
     * 2. Get context from Redis (seeds from DB if cold)
     * 3. Call Anthropic API
     * 4. Save assistant reply to DB
     * 5. Update Redis context
     */
    public function analyze(int $userId, string $message): string
    {
        // 1. Persist user message immediately
        $this->messageRepository->save($userId, 'user', $message);

        // 2. Build system prompt with real financial data
        $context      = $this->aiRepository->getFinancialContext($userId);
        $systemPrompt = AiPrompt::system($context);

        // 3. Get context window from Redis (or seeded from DB)
        $contextMessages = $this->conversationRepository->getContext($userId);

        // 4. Build full messages array: context already has history, just append new message
        //    (we don't duplicate it — getContext returns history BEFORE current message)
        $messages = [...$contextMessages, ['role' => 'user', 'content' => $message]];

        // 5. Call API
        $reply = $this->callApi($systemPrompt, $messages);

        // 6. Persist assistant reply to DB
        $this->messageRepository->save($userId, 'assistant', $reply);

        // 7. Update Redis context with both turns
        $this->conversationRepository->append($userId, $message, $reply);

        return $reply;
    }

    /**
     * Load full chat history from DB for frontend display.
     */
    public function getHistory(int $userId): \Illuminate\Support\Collection
    {
        return $this->messageRepository->getHistory($userId);
    }

    /**
     * Clear both DB and Redis for a user.
     */
    public function clearConversation(int $userId): void
    {
        $this->messageRepository->clearHistory($userId);
        $this->conversationRepository->clear($userId);
    }

    // ── Private ────────────────────────────────────────────────────────────

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
