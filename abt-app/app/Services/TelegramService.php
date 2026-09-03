<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $channelId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->channelId = config('services.telegram.channel_id', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->channelId);
    }

    public function sendPhoto(string $imagePath, ?string $caption = null): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('Telegram not configured. Skipping sendPhoto.');
            return null;
        }

        $response = Http::attach(
            'photo', file_get_contents($imagePath), basename($imagePath)
        )->post("{$this->baseUrl}/sendPhoto", [
            'chat_id' => $this->channelId,
            'caption' => $caption ?? '',
        ]);

        if ($response->successful()) {
            return (string) $response->json('result.message_id');
        }

        Log::error('Telegram sendPhoto failed', ['response' => $response->body()]);
        return null;
    }

    public function editMessageMedia(string $messageId, string $imagePath, ?string $caption = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $response = Http::attach(
            'photo', file_get_contents($imagePath), basename($imagePath)
        )->post("{$this->baseUrl}/editMessageMedia", [
            'chat_id' => $this->channelId,
            'message_id' => $messageId,
            'media' => json_encode([
                'type' => 'photo',
                'media' => 'attach://photo',
                'caption' => $caption ?? '',
            ]),
        ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('Telegram editMessageMedia failed', ['response' => $response->body()]);
        return false;
    }

    public function deleteMessage(string $messageId): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $response = Http::post("{$this->baseUrl}/deleteMessage", [
            'chat_id' => $this->channelId,
            'message_id' => $messageId,
        ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('Telegram deleteMessage failed', ['response' => $response->body()]);
        return false;
    }
}
