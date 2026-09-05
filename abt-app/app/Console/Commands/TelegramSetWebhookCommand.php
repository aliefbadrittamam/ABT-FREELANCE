<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramSetWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook {url? : Domain URL publik HTTPS Anda (contoh: https://domainanda.com)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Telegram Webhook for Invoice Bot and Tournament Bot in production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('url') ?: config('app.url');
        $domain = rtrim($domain, '/');

        if (str_contains($domain, 'localhost') || str_contains($domain, '127.0.0.1')) {
            $this->warn('⚠️ Domain saat ini adalah localhost/127.0.0.1. Telegram webhook membutuhkan URL publik HTTPS.');
            $this->line('Contoh penggunaan saat hosting:');
            $this->info('php artisan telegram:set-webhook https://domainanda.com');
            return Command::FAILURE;
        }

        $this->info("Menghubungkan Webhook ke domain: {$domain}...\n");

        // 1. Invoice Bot
        $invToken = config('services.telegram.bot_token');
        if ($invToken) {
            $invWebhookUrl = "{$domain}/api/telegram/webhook";
            $res1 = Http::post("https://api.telegram.org/bot{$invToken}/setWebhook", [
                'url' => $invWebhookUrl,
                'allowed_updates' => json_encode(['message', 'callback_query']),
            ]);

            if ($res1->successful() && $res1->json('ok')) {
                $this->info("✅ Invoice Bot Webhook BERHASIL diset!");
                $this->line("   Endpoint: {$invWebhookUrl}");
            } else {
                $this->error("❌ Gagal set Webhook Invoice Bot: " . $res1->json('description', 'Error'));
            }
        }

        // 2. Tournament Bot
        $tourToken = config('services.telegram.tournament_bot_token');
        if ($tourToken) {
            $tourWebhookUrl = "{$domain}/api/tournament/webhook";
            $res2 = Http::post("https://api.telegram.org/bot{$tourToken}/setWebhook", [
                'url' => $tourWebhookUrl,
                'allowed_updates' => json_encode(['message', 'callback_query']),
            ]);

            if ($res2->successful() && $res2->json('ok')) {
                $this->info("✅ Tournament Bot Webhook BERHASIL diset!");
                $this->line("   Endpoint: {$tourWebhookUrl}");
            } else {
                $this->error("❌ Gagal set Webhook Tournament Bot: " . $res2->json('description', 'Error'));
            }
        }

        $this->info("\n🚀 Selesai! Kedua bot kini aktif 24 jam otomatis di hosting tanpa perlu menjalankan terminal lagi.");
        return Command::SUCCESS;
    }
}
