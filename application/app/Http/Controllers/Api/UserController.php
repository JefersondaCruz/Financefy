<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function __construct(private UserService $userService)
    {}

    public function index()
    {
        return UserResource::collection($this->userService->index());
    }

    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->store([
            ...$request->validated(),
            'password' => bcrypt($request->validated('password')),
        ]);

        return response()->json(UserResource::make($user)->resolve(), 201);
    }

    public function me(Request $request)
    {
        return response()->json(UserResource::make($request->user())->resolve());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new UserResource($this->userService->show($id));
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $user = $this->userService->update($request->validated(), $id);

        return response()->json(UserResource::make($user)->resolve());
    }

    public function updateProfile(UpdateUserRequest $request)
    {
        $user = $this->userService->update($request->validated(), (string) $request->user()->id);

        return response()->json(UserResource::make($user)->resolve());
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        $this->userService->update([
            'password' => bcrypt($data['password']),
        ], (string) $request->user()->id);

        return response()->json(['message' => 'Senha alterada com sucesso.']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->userService->destroy($id);

        return response()->noContent();
    }

    public function destroyCurrent(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($data['password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['A senha informada está incorreta.'],
            ]);
        }

        $user = $request->user();
        $user->tokens()->delete();
        $this->userService->destroy((string) $user->id);

        return response()->noContent();
    }
}
