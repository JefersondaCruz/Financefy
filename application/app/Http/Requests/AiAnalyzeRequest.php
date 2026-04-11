<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiAnalyzeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message'           => ['required', 'string', 'max:1000'],
            'history'           => ['nullable', 'array'],
            'history.*.role'    => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'A mensagem é obrigatória.',
            'message.max'      => 'A mensagem não pode ter mais de 1000 caracteres.',
            'history.*.role.in' => 'Role inválido no histórico.',
        ];
    }
}
