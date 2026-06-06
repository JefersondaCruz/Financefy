<?php

namespace App\Http\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model) {
        parent::__construct($model);
    }

    public function getAllWithCategory(int $perPage = 10, ?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $query = $this->model
            ->with('category')
            ->where('user_id', auth()->id());

        $this->applyPeriod($query, $startDate, $endDate);
        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('transaction_date')
            ->paginate($perPage);
    }

    public function getAllForExport(string $startDate, string $endDate, array $filters = [])
    {
        $query = $this->model
            ->with('category')
            ->where('user_id', auth()->id());

        $this->applyPeriod($query, $startDate, $endDate);
        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('transaction_date')
            ->get();
    }

    public function getSummary(string $startDate, string $endDate, array $filters = []): array
    {
        $query = $this->model
            ->with('category')
            ->where('user_id', auth()->id())
            ->whereBetween('transaction_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        $this->applyFilters($query, $filters);

        $transactions = $query->get();

        $income = $transactions
            ->filter(fn ($transaction) => $transaction->category?->type === 'income')
            ->sum('amount');

        $expenses = $transactions
            ->filter(fn ($transaction) => $transaction->category?->type === 'expense')
            ->sum('amount');

        $ranking = $transactions
            ->filter(fn ($transaction) => $transaction->category?->type === 'expense')
            ->groupBy(fn ($transaction) => $transaction->category?->name ?? 'Sem categoria')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        return [
            'transaction_count' => $transactions->count(),
            'total_income' => (float) $income,
            'total_expenses' => (float) $expenses,
            'net_balance' => (float) $income - (float) $expenses,
            'monthly_average' => $transactions->count() > 0
                ? (float) $transactions->sum('amount') / $transactions->count()
                : 0,
            'recurring_count' => $transactions->where('is_recurring', true)->count(),
            'category_ranking' => $ranking,
        ];
    }

    private function applyPeriod($query, ?string $startDate, ?string $endDate): void
    {
        if (!$startDate || !$endDate) {
            return;
        }

        $query->whereBetween('transaction_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->whereHas('category', fn ($categoryQuery) =>
                $categoryQuery->where('type', $filters['type'])
            );
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (($filters['recurring'] ?? '') === 'yes') {
            $query->where('is_recurring', true);
        }

        if (($filters['recurring'] ?? '') === 'no') {
            $query->where('is_recurring', false);
        }
    }
}
