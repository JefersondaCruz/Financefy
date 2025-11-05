<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=> 'auth'], function () {
    Route::post('/login', [AuthController::class,'login']);
    Route::post('/register', [AuthController::class,'register']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/users', UserController::class);
});
