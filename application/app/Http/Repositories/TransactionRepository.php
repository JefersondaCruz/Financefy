<?php

namespace App\Http\Repositories;

use App\Models\Transaction;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model) {
        parent::__construct($model);
    }

    public function getAllWithCategory(int $perPage = 10)
    {
        return $this->model
        ->with('category')
        ->where('user_id', auth()->id())
        ->orderByDesc('transaction_date')
        ->paginate($perPage);
    }

}
