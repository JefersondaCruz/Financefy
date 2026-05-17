<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiAnalyzeRequest;
use App\Http\Services\AiService;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function __construct(
        private readonly AiService $aiService,
    ) {}

    public function history(): JsonResponse
    {
        return response()->json(
            $this->aiService->getHistory(auth()->id())
        );
    }

    public function analyze(AiAnalyzeRequest $request): JsonResponse
    {
        return response()->json([
            'reply' =>
            $this->aiService->analyze(
                userId: auth()->id(),
                message: $request->validated('message'),
            )
        ]);
    }

    public function clear(): JsonResponse
    {
        $this->aiService->clearConversation(auth()->id());
        return response()->json(['message' => 'Conversa limpa com sucesso.']);
    }
}
