<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Http\Services\WhatsAppMessageService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookService
{
    private const CHATBOT_ENDPOINT = 'http://n8n:5678/webhook/chatbot';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly WhatsAppMessageService $messageService,
    ) {}

    public function verifyToken(string $mode, string $token, string $challenge): ?string
    {
        $expectedToken = config('services.waba.verify_token');

        if (!hash_equals($expectedToken, $token)) {
            return null;
        }

        return $challenge;
    }

    public function processPayload(array $payload): void
    {
        if (!$this->hasMessages($payload)) {
            return;
        }

        try {
            $message = $payload['entry'][0]['changes'][0]['value']['messages'][0];
            $phone = $message['from'];
            $text = $message['text']['body'] ?? '';

            Log::info('[WABA] Mensagem recebida.', [
                'phone' => $phone,
                'message_id' => $message['id'],
            ]);

            $user = $this->userRepository->getByPhone($phone);

            if ($user === null) {
                $this->handleUnregisteredUser($phone);
                return;
            }

            $this->forwardToChatbot($phone, $text);

        } catch (\Throwable $e) {
            Log::error('[WABA] Erro ao processar payload.', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }

    private function hasMessages(array $payload): bool
    {
        return isset($payload['entry'][0]['changes'][0]['value']['messages'][0]);
    }

    private function handleUnregisteredUser(string $phone): void
    {
        Log::info('[WABA] Usuário não cadastrado.', ['phone' => $phone]);

        $this->messageService->sendMessage(
            $phone,
            "Olá! Não encontramos um cadastro associado ao seu número.\n\n"
        );
    }

    private function forwardToChatbot(string $phone, string $message): void
    {
        try {
            $response = Http::timeout(10)
                ->post(self::CHATBOT_ENDPOINT, [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if ($response->failed()) {
                Log::error('[WABA] Chatbot retornou erro.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }


        } catch (ConnectionException $e) {
            Log::error('[WABA] Falha de conexão com o chatbot.', ['error' => $e->getMessage()]);
        }
    }
}
