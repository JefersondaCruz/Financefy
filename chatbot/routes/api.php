<?php

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});

Route::prefix('webhook')->group(function () {
    Route::get('/whatsapp', [WhatsAppWebhookController::class, 'verify']);
    Route::get('/whatsapp', [WhatsAppWebhookController::class, 'handleWebhook']);
});
