<?php

use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;

Route::group(['prefix'=> 'auth'], function () {
    Route::post('/login', [AuthController::class,'login']);
    Route::post('/register', [AuthController::class,'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'me']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    Route::delete('/user', [UserController::class, 'destroyCurrent']);
    Route::get('/transactions/summary', [TransactionController::class, 'summary']);
    Route::get('/transactions/export', [TransactionController::class, 'exportCsv']);
    Route::get('/transactions', [TransactionController::class, 'get']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('/ai/analyze', [AiController::class, 'analyze']);
    Route::get('/ai/history', [AiController::class, 'history']);
    Route::delete('/ai/conversation', [AiController::class, 'clear']);
});

Route::group(['prefix' => 'webhook'], function () {
    Route::get('/waba',  [WebhookController::class, 'verify']);
    Route::post('/waba', [WebhookController::class, 'handle']);
});
