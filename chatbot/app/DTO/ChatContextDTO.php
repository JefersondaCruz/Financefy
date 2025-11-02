<?php

namespace App\DTO;

use App\Enums\ChatState;

class ChatContextDTO
{
    private array $temporaryData = [];

    public function __construct(
        public readonly int $userId,
        public readonly string $userMessage,
        public ChatState $currentState,
        public readonly string $sessionId
    ) {}

    public function setData(string $key, mixed $value): void
    {
        $this->temporaryData[$key] = $value;
    }

    public function getData(string $key, mixed $default = null): mixed
    {
        return $this->temporaryData[$key] ?? $default;
    }

    public function clearData(): void
    {
        $this->temporaryData = [];
    }

    public function getAllData(): array

    {
        return $this->temporaryData;
    }
}
