<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {
    }

    public function store(CreateTransactionRequest $request)
    {
        return $this->transactionService->store($request->validated());
    }

    public function update(UpdateTransactionRequest $request, string $id)
    {
        return $this->transactionService->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
        return $this->transactionService->destroy($id);
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->transactionService->index(
                $request->get('per_page', 10)
            )
        );
    }

    public function get(Request $request)
    {
        return response()->json(
            $this->transactionService->get(
                $request->get('per_page', 10)
            )
        );
    }
}
