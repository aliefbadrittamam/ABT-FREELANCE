<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TournamentBotService
{
    protected string $botToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.tournament_bot_token', env('TELEGRAM_TOURNAMENT_BOT_TOKEN', ''));
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken);
    }

    public function sendMessage(string $chatId, string $text, array $extra = []): ?int
    {
        if (!$this->isConfigured()) return null;

        try {
            $payload = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $extra);

            $response = Http::timeout(15)->post("{$this->baseUrl}/sendMessage", $payload);
            return $response->successful() ? (int)$response->json('result.message_id') : null;
        } catch (\Exception $e) {
            Log::error('TournamentBot sendMessage error: ' . $e->getMessage());
            return null;
        }
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null): bool
    {
        if (!$this->isConfigured()) return false;

        try {
            $payload = ['callback_query_id' => $callbackQueryId];
            if ($text) $payload['text'] = $text;
            $response = Http::timeout(10)->post("{$this->baseUrl}/answerCallbackQuery", $payload);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUpdates(int $offset = 0, int $timeout = 25): array
    {
        if (!$this->isConfigured()) return [];

        try {
            $response = Http::timeout($timeout + 10)->get("{$this->baseUrl}/getUpdates", [
                'offset' => $offset,
                'timeout' => $timeout,
                'allowed_updates' => json_encode(['message', 'callback_query']),
            ]);

            return $response->successful() ? $response->json('result', []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
