<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $channelId;
    protected string $baseUrl;
    protected ?string $lastError = null;

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

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function sendPhoto(string $imagePath, ?string $caption = null): ?string
    {
        $this->lastError = null;

        if (!$this->isConfigured()) {
            $this->lastError = 'Token Bot Telegram (TELEGRAM_BOT_TOKEN) atau Channel ID belum dikonfigurasi di file .env.';
            Log::warning($this->lastError);
            return null;
        }

        if (!file_exists($imagePath)) {
            $this->lastError = 'File gambar tidak ditemukan di server.';
            Log::error($this->lastError, ['path' => $imagePath]);
            return null;
        }

        try {
            $response = Http::timeout(25)->attach(
                'photo', file_get_contents($imagePath), basename($imagePath)
            )->post("{$this->baseUrl}/sendPhoto", [
                'chat_id' => $this->channelId,
                'caption' => $caption ?? '',
            ]);

            if ($response->successful()) {
                return (string) $response->json('result.message_id');
            }

            $errorCode = $response->json('error_code');
            $description = $response->json('description', 'Unknown Telegram Error');
            $this->lastError = "Telegram API Error [{$errorCode}]: {$description}";
            Log::error('Telegram sendPhoto failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            $this->lastError = 'Gagal menghubungi server Telegram: ' . $e->getMessage();
            Log::error($this->lastError);
            return null;
        }
    }

    public function editMessageMedia(string $messageId, string $imagePath, ?string $caption = null): bool
    {
        $this->lastError = null;

        if (!$this->isConfigured()) {
            $this->lastError = 'Telegram belum dikonfigurasi.';
            return false;
        }

        if (!file_exists($imagePath)) {
            $this->lastError = 'File gambar untuk update tidak ditemukan.';
            return false;
        }

        try {
            $response = Http::timeout(25)->attach(
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

            $errorCode = $response->json('error_code');
            $description = $response->json('description', 'Unknown Telegram Error');
            $this->lastError = "Telegram Edit Media Error [{$errorCode}]: {$description}";
            Log::error('Telegram editMessageMedia failed', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            $this->lastError = 'Gagal menghubungi server Telegram: ' . $e->getMessage();
            Log::error($this->lastError);
            return false;
        }
    }

    public function deleteMessage(string $messageId): bool
    {
        $this->lastError = null;

        if (!$this->isConfigured()) {
            $this->lastError = 'Telegram belum dikonfigurasi.';
            return false;
        }

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/deleteMessage", [
                'chat_id' => $this->channelId,
                'message_id' => $messageId,
            ]);

            if ($response->successful()) {
                return true;
            }

            $errorCode = $response->json('error_code');
            $description = $response->json('description', 'Unknown Telegram Error');
            $this->lastError = "Telegram Delete Message Error [{$errorCode}]: {$description}";
            Log::error('Telegram deleteMessage failed', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            $this->lastError = 'Gagal menghubungi server Telegram: ' . $e->getMessage();
            Log::error($this->lastError);
            return false;
        }
    }
}
