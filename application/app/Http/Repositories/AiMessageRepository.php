<?php

namespace App\Http\Repositories;

use App\Models\AiMessage;
use Illuminate\Support\Collection;

class AiMessageRepository
{

    public function getHistory(int $userId): Collection
    {
        return AiMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'role', 'content', 'created_at']);
    }

    public function getLastMessages(int $userId, int $limit = 20): array
    {
        return AiMessage::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();
    }

    public function save(int $userId, string $role, string $content): AiMessage
    {
        return AiMessage::create([
            'user_id' => $userId,
            'role'    => $role,
            'content' => $content,
        ]);
    }

    public function clearHistory(int $userId): void
    {
        AiMessage::where('user_id', $userId)->delete();
    }
}
