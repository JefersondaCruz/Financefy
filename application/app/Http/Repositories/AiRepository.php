<?php

namespace App\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;

class AiRepository
{
    /**
     * Fetch and aggregate the user's financial data for the last 3 months.
     */
    public function getFinancialContext(int $userId): array
    {
        $since = Carbon::now()->subMonths(3)->startOfMonth();

        $transactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->where('transaction_date', '>=', $since)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalExpense = $transactions->where('category.type', 'expense')->sum('amount');
        $totalIncome  = $transactions->where('category.type', 'income')->sum('amount');
        $netBalance   = $totalIncome - $totalExpense;

        return [
            'totalExpense' => $totalExpense,
            'totalIncome'  => $totalIncome,
            'netBalance'   => $netBalance,
            'byCategory'   => $this->groupByCategory($transactions, $totalExpense),
            'byMonth'      => $this->groupByMonth($transactions),
            'topExpenses'  => $this->topExpenses($transactions),
            'recurring'    => $this->recurringTransactions($transactions),
        ];
    }

    // ── Private aggregators ────────────────────────────────────────────────

    private function groupByCategory($transactions, float $totalExpense): array
    {
        return $transactions
            ->where('category.type', 'expense')
            ->groupBy('category.name')
            ->map(fn($group) => [
                'name'  => $group->first()->category->name,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'pct'   => $totalExpense > 0
                    ? round(($group->sum('amount') / $totalExpense) * 100, 1)
                    : 0,
            ])
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }

    private function groupByMonth($transactions): array
    {
        return $transactions
            ->groupBy(fn($t) => Carbon::parse($t->transaction_date)->format('Y-m'))
            ->map(fn($group, $key) => [
                'month'   => Carbon::createFromFormat('Y-m', $key)->translatedFormat('F/Y'),
                'income'  => $group->where('category.type', 'income')->sum('amount'),
                'expense' => $group->where('category.type', 'expense')->sum('amount'),
            ])
            ->sortKeys()
            ->values()
            ->toArray();
    }

    private function topExpenses($transactions): array
    {
        return $transactions
            ->where('category.type', 'expense')
            ->sortByDesc('amount')
            ->take(5)
            ->map(fn($t) => [
                'description' => $t->description,
                'amount'      => $t->amount,
                'category'    => $t->category->name,
                'date'        => Carbon::parse($t->transaction_date)->format('d/m/Y'),
            ])
            ->values()
            ->toArray();
    }

    private function recurringTransactions($transactions): array
    {
        return $transactions
            ->where('is_recurring', true)
            ->map(fn($t) => [
                'description' => $t->description,
                'amount'      => $t->amount,
                'type'        => $t->category->type,
            ])
            ->values()
            ->toArray();
    }
}
