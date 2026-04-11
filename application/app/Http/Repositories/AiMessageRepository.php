<?php

namespace App\Repositories;

use App\Models\AiMessage;
use Illuminate\Support\Collection;

class AiMessageRepository
{
    /**
     * Fetch all messages for a user, ordered by creation (for display).
     */
    public function getHistory(int $userId): Collection
    {
        return AiMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'role', 'content', 'created_at']);
    }

    /**
     * Fetch only the last N messages (for seeding Redis context).
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function getLastMessages(int $userId, int $limit = 20): array
    {
        return AiMessage::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['role', 'content'])
            ->reverse()                        // back to chronological order
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();
    }

    /**
     * Persist a single message.
     */
    public function save(int $userId, string $role, string $content): AiMessage
    {
        return AiMessage::create([
            'user_id' => $userId,
            'role'    => $role,
            'content' => $content,
        ]);
    }

    /**
     * Delete all messages for a user.
     */
    public function clearHistory(int $userId): void
    {
        AiMessage::where('user_id', $userId)->delete();
    }
}
