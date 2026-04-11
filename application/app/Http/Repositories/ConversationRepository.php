<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Redis;

class ConversationRepository
{
    private const PREFIX       = 'ai_conversation:';
    private const TTL          = 60 * 60 * 24 * 2; // 2 dias — só cache de contexto
    private const MAX_MESSAGES = 40;                // 20 turnos

    public function __construct(
        private readonly AiMessageRepository $messageRepository,
    ) {}

    /**
     * Retorna a janela de contexto para a API.
     * Se o Redis estiver frio (key ausente), recarrega do banco.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function getContext(int $userId): array
    {
        $raw = Redis::get($this->key($userId));

        if ($raw !== null) {
            return json_decode($raw, true);
        }

        // Cache miss — seed from DB
        $messages = $this->messageRepository->getLastMessages($userId, self::MAX_MESSAGES);
        $this->store($userId, $messages);

        return $messages;
    }

    /**
     * Adiciona um novo turno user+assistant e persiste no Redis.
     */
    public function append(int $userId, string $userMessage, string $assistantReply): void
    {
        $context   = $this->getContext($userId);
        $context[] = ['role' => 'user',      'content' => $userMessage];
        $context[] = ['role' => 'assistant', 'content' => $assistantReply];

        // Janela deslizante — descarta os mais antigos se ultrapassar o limite
        if (count($context) > self::MAX_MESSAGES) {
            $context = array_slice($context, count($context) - self::MAX_MESSAGES);
        }

        $this->store($userId, $context);
    }

    /**
     * Limpa o cache Redis do usuário.
     * O banco é limpo separadamente via AiMessageRepository.
     */
    public function clear(int $userId): void
    {
        Redis::del($this->key($userId));
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function store(int $userId, array $messages): void
    {
        Redis::setex($this->key($userId), self::TTL, json_encode($messages));
    }

    private function key(int $userId): string
    {
        return self::PREFIX . $userId;
    }
}
