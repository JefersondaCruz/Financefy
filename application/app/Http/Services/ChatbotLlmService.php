<?php

namespace App\Http\Services;

use OpenAI;

class ChatbotLlmService
{
    private const MAX_TOKENS = 1024;

    private mixed $client;

    public function __construct()
    {
        $key = config('services.openai.key');
        $baseUrl = config('services.openai.base_url');

        $this->client = $baseUrl
            ? OpenAI::factory()
                ->withApiKey($key)
                ->withBaseUri($baseUrl)
                ->make()
            : OpenAI::client($key);
    }

    public function chat(array $messages, string $toolChoice = 'auto'): array
    {
        $response = $this->client->chat()->create([
            'model' => config('services.openai.chatbot_model', 'gpt-4o-mini'),
            'max_tokens' => self::MAX_TOKENS,
            'messages' => $messages,
            'tools' => $this->tools(),
            'tool_choice' => $toolChoice,
        ]);

        $data = method_exists($response, 'toArray') ? $response->toArray() : (array) $response;

        return $data['choices'][0]['message'] ?? [];
    }

    private function tools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'consultar_categorias',
                    'description' => 'Lista as categorias financeiras disponiveis para o usuario.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'registrar_transacao',
                    'description' => 'Registra uma despesa ou receita do usuario.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'category_id' => [
                                'type' => ['integer', 'null'],
                                'description' => 'ID da categoria escolhida apos consultar categorias.',
                            ],
                            'category' => [
                                'type' => ['string', 'null'],
                                'description' => 'Nome sugerido quando nenhuma categoria existente se encaixar.',
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Descricao curta da transacao.',
                            ],
                            'amount' => [
                                'type' => 'number',
                                'description' => 'Valor positivo da transacao.',
                            ],
                            'transaction_date' => [
                                'type' => 'string',
                                'format' => 'date',
                                'description' => 'Data da transacao em YYYY-MM-DD.',
                            ],
                            'payment_method' => [
                                'type' => 'string',
                                'enum' => ['credit_card', 'pix', 'money', 'others'],
                            ],
                            'is_recurring' => [
                                'type' => 'boolean',
                            ],
                            'recurrence_type' => [
                                'type' => ['string', 'null'],
                            ],
                        ],
                        'required' => ['description', 'amount'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'consultar_transacoes',
                    'description' => 'Consulta transacoes e resumo financeiro do usuario por periodo e filtros.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'start_date' => [
                                'type' => ['string', 'null'],
                                'format' => 'date',
                            ],
                            'end_date' => [
                                'type' => ['string', 'null'],
                                'format' => 'date',
                            ],
                            'per_page' => [
                                'type' => ['integer', 'null'],
                                'minimum' => 1,
                                'maximum' => 30,
                            ],
                            'search' => [
                                'type' => ['string', 'null'],
                            ],
                            'type' => [
                                'type' => ['string', 'null'],
                                'enum' => ['expense', 'income', null],
                            ],
                            'category_id' => [
                                'type' => ['integer', 'null'],
                            ],
                            'payment_method' => [
                                'type' => ['string', 'null'],
                                'enum' => ['credit_card', 'pix', 'money', 'others', null],
                            ],
                            'recurring' => [
                                'type' => ['string', 'null'],
                                'enum' => ['yes', 'no', null],
                            ],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];
    }
}
