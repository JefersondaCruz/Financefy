<?php

namespace App\Flows;

use App\DTO\ChatContextDTO;
use App\Enums\ChatState;

abstract class BaseFlow
{
    abstract public function handle(ChatContextDTO $context): array;

    abstract public function getInitialState(): ChatState;

    protected function sendMessage(string $message, ?ChatState $nextState = null): array
    {
        return [
            'message' => $message,
            'next_state' => $nextState,
        ];
    }
}
