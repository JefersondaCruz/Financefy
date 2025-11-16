<?php

namespace App\Http\Services;

use App\Http\Repositories\TransactionRepository;

class TransactionService extends BaseService
{
    public function __construct(protected TransactionRepository $transactionRepository)
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
        $data['user_id'] = auth()->id();
        return $this->transactionRepository->store($data);
    }

    public function get(int $perPage = 10)
    {
        return $this->transactionRepository->getAllWithCategory($perPage);

    }

}
