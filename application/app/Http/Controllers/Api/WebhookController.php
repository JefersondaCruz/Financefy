<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\VerifyWebhookRequest;
use App\Http\Services\WhatsAppWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WhatsAppWebhookService $webhookService
    ) {}

    public function verify(VerifyWebhookRequest $request): Response
    {
        $challenge = $this->webhookService->verifyToken(
            $request->query('hub_mode'),
            $request->query('hub_verify_token'),
            $request->query('hub_challenge'),
        );

        if ($challenge === null) {
            return response('Forbidden', 403);
        }

        return response($challenge, 200);
    }

    public function handle(Request $request): JsonResponse
    {
        $this->webhookService->processPayload($request->all());
        return response()->json(['status' => 'received'], 200);
    }
}
