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
        $data['user_id'] = auth()->id()
            ?? $this->userRepository->getByPhone($data['phone'])?->id
            ?? throw new \Exception('Usuário não encontrado');

        if (!isset($data['category_id']) && isset($data['category'])) {

            $category = $this->categoryRepository->findByName($data['category'])
                ?? throw new \Exception('Categoria não encontrada');

            $data['category_id'] = $category->id;
        }

        unset($data['category']);

        return new TransactionResource($this->transactionRepository->store($data)->load('category'));
    }

    public function get(int $perPage, string $startDate, string $endDate)
    {
        $transactions = $this->transactionRepository->getAllWithCategory($perPage, $startDate, $endDate);

        return $this->formatPaginatedTransactions($transactions);
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
