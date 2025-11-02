<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $accessToken;
    private string $phoneNumberId;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Envia mensagem de texto
     */
    public function sendTextMessage(string $to, string $message): array
    {
        $url = "{$this->apiUrl}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ];

        return $this->makeRequest($url, $payload);
    }

    /**
     * Envia mensagem com botões interativos
     */
    public function sendInteractiveButtons(string $to, string $bodyText, array $buttons): array
    {
        $url = "{$this->apiUrl}/{$this->phoneNumberId}/messages";

        // Formata os botões (máximo 3)
        $formattedButtons = array_map(function ($button, $index) {
            return [
                'type' => 'reply',
                'reply' => [
                    'id' => $button['id'] ?? "btn_{$index}",
                    'title' => substr($button['title'], 0, 20), // Max 20 chars
                ],
            ];
        }, array_slice($buttons, 0, 3), array_keys($buttons));

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $bodyText,
                ],
                'action' => [
                    'buttons' => $formattedButtons,
                ],
            ],
        ];

        return $this->makeRequest($url, $payload);
    }

    /**
     * Envia mensagem com lista (menu)
     */
    public function sendInteractiveList(string $to, string $bodyText, string $buttonText, array $sections): array
    {
        $url = "{$this->apiUrl}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $bodyText,
                ],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections,
                ],
            ],
        ];

        return $this->makeRequest($url, $payload);
    }

    private function makeRequest(string $url, array $payload): array
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('WhatsApp API Success', [
                    'response' => $response->json(),
                ]);

                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('WhatsApp API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp API Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }


    /**
     * Valida formato de resposta do webhook
     */
    public function isValidWebhookPayload(array $payload): bool
    {
        return isset($payload['object'])
            && $payload['object'] === 'whatsapp_business_account'
            && isset($payload['entry']);
    }

    /**
     * Extrai mensagem do payload do webhook
     */
    public function extractMessageFromWebhook(array $payload): ?array
    {
        if (!$this->isValidWebhookPayload($payload)) {
            return null;
        }

        $entry = $payload['entry'][0] ?? null;
        $changes = $entry['changes'][0] ?? null;
        $value = $changes['value'] ?? null;
        $messages = $value['messages'] ?? [];

        if (empty($messages)) {
            return null;
        }

        $message = $messages[0];

        return [
            'message_id' => $message['id'],
            'from' => $message['from'], // Número do remetente
            'timestamp' => $message['timestamp'],
            'type' => $message['type'], // text, image, button, etc
            'text' => $message['text']['body'] ?? null,
            'button_reply' => $message['interactive']['button_reply'] ?? null,
            'list_reply' => $message['interactive']['list_reply'] ?? null,
            'name' => $value['contacts'][0]['profile']['name'] ?? 'Usuário',
        ];
    }
}
