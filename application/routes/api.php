<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=> 'auth'], function () {
    Route::post('/login', [AuthController::class,'login']);
    Route::post('/register', [AuthController::class,'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'get']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
    Route::get('/categories', [CategoryController::class, 'index']);
});
