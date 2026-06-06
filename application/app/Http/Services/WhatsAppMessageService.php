<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppMessageService
{
    private string $apiUrl;
    private string $accessToken;
    private string $phoneNumberId;

    public function __construct()
    {
        $this->apiUrl = config('services.waba.api_url');
        $this->accessToken = config('services.waba.access_token');
        $this->phoneNumberId = config('services.waba.phone_number_id');
    }

    public function sendMessage(string $phone, string $message): void
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

            if ($response->failed()) {
                Log::error('[WABA] Falha ao enviar mensagem.', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }

            Log::info('[WABA] Mensagem enviada ao usuário.', ['phone' => $phone]);

        } catch (\Throwable $e) {
            Log::error('[WABA] Exceção ao enviar mensagem.', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
