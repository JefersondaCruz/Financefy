<?php

namespace App\Http\Repositories;

use Illuminate\Support\Facades\Redis;
use App\Http\Repositories\AiMessageRepository;

class ConversationRepository
{
    private const PREFIX = 'ai_conversation:';
    private const TTL = 60 * 60 * 24 * 2;
    private const MAX_MESSAGES = 40;

    public function __construct(
        private readonly AiMessageRepository $messageRepository,
    ) {}

    public function getContext(int $userId): array
    {
        $raw = Redis::get($this->key($userId));

        if ($raw !== null) {
            return json_decode($raw, true);
        }

        $messages = $this->messageRepository->getLastMessages($userId, self::MAX_MESSAGES);
        $this->store($userId, $messages);

        return $messages;
    }

    public function append(int $userId, string $userMessage, string $assistantReply): void
    {
        $context = $this->getContext($userId);
        $context[] = ['role' => 'user', 'content' => $userMessage];
        $context[] = ['role' => 'assistant', 'content' => $assistantReply];

        if (count($context) > self::MAX_MESSAGES) {
            $context = array_slice($context, count($context) - self::MAX_MESSAGES);
        }

        $this->store($userId, $context);
    }

    public function clear(int $userId): void
    {
        Redis::del($this->key($userId));
    }

    private function store(int $userId, array $messages): void
    {
        Redis::setex($this->key($userId), self::TTL, json_encode($messages));
    }

    private function key(int $userId): string
    {
        return self::PREFIX . $userId;
    }
}
