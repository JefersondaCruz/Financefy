<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Services\UserService;
use App\Http\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $AuthService, private UserService $userService)
    {}

    public function login(LoginRequest $request)
    {
        return response()->json(['data' => $this->AuthService->login($request->validated())]);
    }

    public function logout()
    {
        $this->AuthService->logout();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function register(CreateUserRequest $request)
    {
        return response()->json(['data' => $this->AuthService->register($request->validated())]);
    }
}
