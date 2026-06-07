<?php

namespace App\Http\Services;

use App\Http\Repositories\CategoryRepository;
use App\Http\Repositories\TransactionRepository;
use App\Models\User;

class ChatbotToolService
{
    private const DEFAULT_PER_PAGE = 10;

    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected TransactionRepository $transactionRepository
    ) {}

    public function consultarCategorias(User $user): array
    {
        return $this->categoryRepository
            ->indexForUser($user->id)
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ])
            ->values()
            ->all();
    }

    public function registrarTransacao(User $user, array $arguments): array
    {
        $data = $this->transactionData($user, $arguments);
        $suggestedCategory = !$data['category_id'] ? ($arguments['category'] ?? null) : null;
        $transaction = $this->transactionRepository
            ->storeForUser($user->id, $data)
            ->load('category');
        $transaction->suggested_category = $suggestedCategory;

        return $this->formatTransaction($transaction);
    }

    public function consultarTransacoes(User $user, array $arguments): array
    {
        $perPage = (int) ($arguments['per_page'] ?? self::DEFAULT_PER_PAGE);
        $startDate = $arguments['start_date'] ?? null;
        $endDate = $arguments['end_date'] ?? null;
        $filters = $this->filtersFrom($arguments);

        $transactions = $this->transactionRepository->getAllWithCategoryForUser(
            $user->id,
            $perPage,
            $startDate,
            $endDate,
            $filters
        );

        $summary = null;

        if ($startDate && $endDate) {
            $summary = $this->summaryForUser($user, $startDate, $endDate, $filters);
        }

        return [
            'transactions' => $transactions
                ->getCollection()
                ->map(fn ($transaction) => $this->formatTransaction($transaction))
                ->values()
                ->all(),
            'summary' => $summary,
        ];
    }

    private function transactionData(User $user, array $arguments): array
    {
        $categoryId = $arguments['category_id'] ?? null;

        if ($categoryId) {
            $categoryId = $this->categoryRepository->findForUser((int) $categoryId, $user->id)?->id;
        }

        if (!$categoryId && !empty($arguments['category'])) {
            $category = $this->categoryRepository->findByNameForUser(
                $arguments['category'],
                $user->id
            );

            $categoryId = $category?->id;
        }

        return [
            'category_id' => $categoryId,
            'description' => $arguments['description'],
            'amount' => $arguments['amount'],
            'transaction_date' => $arguments['transaction_date'] ?? now()->toDateString(),
            'payment_method' => $arguments['payment_method'] ?? 'others',
            'is_recurring' => $arguments['is_recurring'] ?? false,
            'recurrence_type' => $arguments['recurrence_type'] ?? null,
        ];
    }

    private function summaryForUser(User $user, string $startDate, string $endDate, array $filters): array
    {
        return collect($this->transactionRepository->getSummaryForUser(
            $user->id,
            $startDate,
            $endDate,
            $filters
        ))
            ->only(['total_income', 'total_expenses', 'net_balance', 'transaction_count'])
            ->all();
    }

    private function formatTransaction($transaction): array
    {
        return [
            'description' => $transaction->description,
            'amount' => (float) $transaction->amount,
            'amount_formatted' => $this->formatMoney((float) $transaction->amount),
            'transaction_date' => $transaction->transaction_date,
            'payment_method' => $transaction->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($transaction->payment_method),
            'category' => $transaction->category ? [
                'name' => $transaction->category->name,
                'type' => $transaction->category->type,
            ] : null,
            'suggested_category' => $transaction->suggested_category ?? null,
        ];
    }

    private function formatMoney(float $amount): string
    {
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'credit_card' => 'cartao de credito',
            'pix' => 'Pix',
            'money' => 'dinheiro',
            default => 'outros',
        };
    }

    private function filtersFrom(array $arguments): array
    {
        return collect($arguments)
            ->only(['search', 'type', 'category_id', 'payment_method', 'recurring'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }
}
