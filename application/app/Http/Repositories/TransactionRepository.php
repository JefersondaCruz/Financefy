<?php

namespace App\Http\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model) {
        parent::__construct($model);
    }

    public function getAllWithCategory(int $perPage = 10, string $startDate, string $endDate)
    {
        return $this->model
            ->with('category')
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->orderByDesc('transaction_date')
            ->paginate($perPage);
    }

}
