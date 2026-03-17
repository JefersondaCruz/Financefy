<?php

namespace App\Http\Services;

use App\Http\Repositories\CategoryRepository;
use App\Http\Repositories\TransactionRepository;
use App\Http\Repositories\UserRepository;

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

        // TODO: Criar Resource
        $transactions->getCollection()->transform(function ($t) {
            return [
                'title' => $t->description,
                'date' => $t->transaction_date,
                'amount' => (float) $t->amount,
                'method' => $t->payment_method,
                'category' => $t->category->name,
                'type' => $t->category->type,
            ];
        });

        return $transactions;
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

        return $this->transactionRepository->store($data);
    }

    public function get(int $perPage, string $startDate, string $endDate)
    {
        return $this->transactionRepository->getAllWithCategory($perPage, $startDate, $endDate);
    }

}
