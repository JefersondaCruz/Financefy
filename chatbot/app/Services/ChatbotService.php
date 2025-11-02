<?php

namespace App\Services;

use App\Enums\ChatState;
use App\DTO\ChatContextDTO;
use App\Flows\AuthFlow;
use App\Flows\ProductFlow;

class ChatbotService
{
    public function __construct(
        private ChatSessionService $sessionService,
        private AuthFlow $authFlow,
        private ProductFlow $productFlow,
    ){}

    public function processMessage(int $userId, string $message): array
    {
        $session = $this->sessionService->getOrCreate($userId);

        $context = new ChatContextDTO(
            userId: $userId,
            userMessage: $message,
            currentState: ChatState::from($session->current_state),
            sessionId: $session->id,
        );

        $response = $this->routeToFlow($context);

        if (isset($reponse['next_state'])) {
            $this->sessionService->updateState($userId, $response['next_state']);
        }

        return $response;
    }

    public function routeToFlow(ChatContextDTO $context): array
    {
        return match(true){
            // Auth States
            in_array($context->currentState, [
                ChatState::AWAITING_LOGIN_EMAIL,
                ChatState::AWAITING_LOGIN_PASSWORD,
                ChatState::AWAITING_REGISTER_NAME,
            ]) => $this->authFlow->handle($context),

            // product states
            in_array($context->currentState,[
                ChatState::AWAITING_PRODUCT_NAME,
                ChatState::AWAITING_PRODUCT_VALUE,
            ]) => $this->productFlow->handle($context),

            ChatState::MAIN_MENU => $this->handleMainMenu($context),

            default => ['message' => 'Estado desconhecido', 'next_state' => ChatState::MAIN_MENU->value],
        };
    }

    private function handleMainMenu(ChatContextDTO $context): array
    {
        return match($context->userMessage) {
            '1' => [
                'message' => '🛒 Vamos cadastrar um produto!\n\nQual o nome do produto?',
                'next_state' => ChatState::AWAITING_PRODUCT_NAME->value
            ],
            '2' => [
                'message' => '📊 Aqui estão seus produtos...',
                'next_state' => ChatState::MAIN_MENU->value
            ],
            default => [
                'message' => 'Opção inválida. Escolha uma opção válida.',
                'next_state' => ChatState::MAIN_MENU->value
            ]
        };
    }
}
