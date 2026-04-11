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

    public function history(): JsonResponse
    {
        $messages = $this->aiService->getHistory(auth()->id());

        return response()->json($messages);
    }

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

    public function clear(): JsonResponse
    {
        $this->aiService->clearConversation(auth()->id());

        return response()->json(['message' => 'Conversa limpa com sucesso.']);
    }
}
