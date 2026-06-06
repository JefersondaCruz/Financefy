<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'transaction_date' => $this->transaction_date,
            'payment_method' => $this->payment_method,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                if (!$this->category) {
                    return null;
                }

                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'type' => $this->category->type,
                    'user_id' => $this->category->user_id,
                ];
            }),
            'is_recurring' => (bool) $this->is_recurring,
            'recurrence_type' => $this->recurrence_type,
        ];
    }
}
