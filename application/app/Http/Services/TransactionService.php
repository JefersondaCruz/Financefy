<?php

namespace App\Http\Services;

use App\Http\Repositories\CategoryRepository;
use App\Http\Repositories\TransactionRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\TransactionResource;

class TransactionService extends BaseService
{
    public function __construct(
        protected TransactionRepository $transactionRepository,
        protected UserRepository $userRepository,
        protected CategoryRepository $categoryRepository
    )

    {
        parent::__construct($transactionRepository);
    }

    public function index(int $perPage = 10)
    {
        $transactions = $this->transactionRepository->getAllWithCategory($perPage);

        return $this->formatPaginatedTransactions($transactions);
    }

    public function store(array $data)
    {
        $phone = $data['phone'] ?? null;
        $userId = auth()->id()
            ?? ($phone ? $this->userRepository->getByPhone($phone)?->id : null)
            ?? throw new \Exception('Usuário não encontrado');

        $data['user_id'] = $userId;

        if (!empty($data['category_id'])) {
            $data['category_id'] = $this->categoryRepository
                ->findForUser((int) $data['category_id'], $userId)?->id
                ?? throw new \Exception('Categoria não encontrada');
        }

        if (empty($data['category_id']) && !empty($data['category'])) {
            $data['category_id'] = $this->categoryRepository
                ->findByNameForUser($data['category'], $userId)?->id;
        }

        unset($data['category'], $data['phone']);

        return new TransactionResource($this->transactionRepository->store($data)->load('category'));
    }

    public function get(int $perPage, string $startDate, string $endDate)
    {
        $transactions = $this->transactionRepository->getAllWithCategory($perPage, $startDate, $endDate);

        return $this->formatPaginatedTransactions($transactions);
    }

    public function getFiltered(int $perPage, string $startDate, string $endDate, array $filters = [])
    {
        $transactions = $this->transactionRepository->getAllWithCategory($perPage, $startDate, $endDate, $filters);

        return $this->formatPaginatedTransactions($transactions);
    }

    public function summary(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->transactionRepository->getSummary($startDate, $endDate, $filters);
    }

    public function exportCsv(string $startDate, string $endDate, array $filters = []): string
    {
        $transactions = $this->transactionRepository->getAllForExport($startDate, $endDate, $filters);
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Descricao', 'Categoria', 'Tipo', 'Pagamento', 'Data', 'Valor', 'Recorrente', 'Recorrencia']);

        foreach ($transactions as $transaction) {
            fputcsv($handle, [
                $transaction->description,
                $transaction->category?->name ?? 'Sem categoria',
                $transaction->category?->type === 'income' ? 'Receita' : 'Despesa',
                $transaction->payment_method,
                $transaction->transaction_date,
                number_format((float) $transaction->amount, 2, '.', ''),
                $transaction->is_recurring ? 'Sim' : 'Nao',
                $transaction->recurrence_type ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . $csv;
    }

    public function update(array $data, string $id)
    {
        return new TransactionResource($this->transactionRepository->update($data, $id)->load('category'));
    }

    private function formatPaginatedTransactions($transactions)
    {
        $transactions->getCollection()->transform(
            fn ($transaction) => (new TransactionResource($transaction))->resolve()
        );

        return $transactions;
    }

}
