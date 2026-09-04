<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramBotHandler;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Handle incoming webhook requests from Telegram in production.
     */
    public function handle(Request $request, TelegramBotHandler $handler)
    {
        $update = $request->all();

        if (isset($update['message'])) {
            try {
                $handler->handleMessage($update['message']);
            } catch (\Exception $e) {
                Log::error('Telegram webhook message error: ' . $e->getMessage());
            }
        }

        if (isset($update['callback_query'])) {
            try {
                $handler->handleCallbackQuery($update['callback_query']);
            } catch (\Exception $e) {
                Log::error('Telegram webhook callback error: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
