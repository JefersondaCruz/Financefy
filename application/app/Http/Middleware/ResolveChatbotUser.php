<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ResolveChatbotUser
{
    public function handle(Request $request, Closure $next)
    {
        $phone = $request->header('X-Chatbot-Phone')
            ?? $request->input('phone');

        if (!$phone) {
            return response()->json(['error' => 'Phone required'], 401);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
