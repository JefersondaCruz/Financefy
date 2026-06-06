<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:1',
            'search' => 'sometimes|nullable|string|max:255',
            'type' => 'sometimes|nullable|in:income,expense',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'payment_method' => 'sometimes|nullable|in:credit_card,pix,money,others',
            'recurring' => 'sometimes|nullable|in:yes,no',
        ];
    }
}
