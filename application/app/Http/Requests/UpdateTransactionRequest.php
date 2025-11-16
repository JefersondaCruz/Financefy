<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'transaction_date' => 'sometimes|date|before_or_equal:today',
            'payment_method' => 'sometimes|in:credit_card,pix,money,others',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_type' => 'sometimes|nullable|string|max:255',
        ];
    }
}
