<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TournamentBotHandler;
use Illuminate\Support\Facades\Log;

class TournamentWebhookController extends Controller
{
    /**
     * Handle incoming webhook requests from Tournament Bot in production.
     */
    public function handle(Request $request, TournamentBotHandler $handler)
    {
        $update = $request->all();

        if (isset($update['message'])) {
            try {
                $handler->handleMessage($update['message']);
            } catch (\Exception $e) {
                Log::error('Tournament webhook message error: ' . $e->getMessage());
            }
        }

        if (isset($update['callback_query'])) {
            try {
                $handler->handleCallbackQuery($update['callback_query']);
            } catch (\Exception $e) {
                Log::error('Tournament webhook callback error: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
