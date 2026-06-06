<?php

namespace App\Http\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model) {
        parent::__construct($model);
    }

    public function getAllWithCategory(int $perPage = 10, ?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->model
            ->with('category')
            ->where('user_id', auth()->id());

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        return $query
            ->orderByDesc('transaction_date')
            ->paginate($perPage);
    }
}
