<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class VerifyWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function all($keys = null): array
    {
        return array_merge(parent::all($keys), $this->query());
    }

    public function rules(): array
    {
        return [
            'hub_mode' => ['required', 'string'],
            'hub_verify_token' => ['required', 'string'],
            'hub_challenge' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'hub_mode.required' => 'O parâmetro hub.mode é obrigatório.',
            'hub_verify_token.required' => 'O parâmetro hub.verify_token é obrigatório.',
            'hub_challenge.required' => 'O parâmetro hub.challenge é obrigatório.',
        ];
    }
}
