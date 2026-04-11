<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiAnalyzeRequest;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function __construct(
        private readonly AiService $aiService,
    ) {}

    /**
     * Return the full chat history from DB for the authenticated user.
     */
    public function history(): JsonResponse
    {
        $messages = $this->aiService->getHistory(auth()->id());

        return response()->json($messages);
    }

    /**
     * Send a message. History context is managed server-side via Redis + DB.
     */
    public function analyze(AiAnalyzeRequest $request): JsonResponse
    {
        try {
            $reply = $this->aiService->analyze(
                userId:  auth()->id(),
                message: $request->validated('message'),
            );

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            report($e);

            return response()->json(
                ['error' => 'Erro ao processar sua mensagem. Tente novamente.'],
                500
            );
        }
    }

    /**
     * Clear full history: both DB and Redis.
     */
    public function clear(): JsonResponse
    {
        $this->aiService->clearConversation(auth()->id());

        return response()->json(['message' => 'Conversa limpa com sucesso.']);
    }
}
