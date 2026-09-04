<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Services\TelegramBotHandler;

class TelegramListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:listen {--once : Run only one polling cycle and exit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen and process incoming messages from Telegram Bot in real-time (Long Polling)';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram, TelegramBotHandler $handler)
    {
        if (!$telegram->isConfigured()) {
            $this->error('Telegram bot belum dikonfigurasi di file .env.');
            return Command::FAILURE;
        }

        $this->info('⚡ Telegram Bot Listener aktif dan siap menerima perintah...');
        $this->info('💡 Ketik /inv di chat bot Telegram Anda untuk membuat invoice.');
        $this->info('Tekan CTRL+C untuk menghentikan listener.');

        $offset = 0;
        $runOnce = $this->option('once');

        while (true) {
            $updates = $telegram->getUpdates($offset, 15);

            foreach ($updates as $update) {
                $updateId = $update['update_id'];
                $offset = $updateId + 1;

                if (isset($update['message'])) {
                    $user = $update['message']['from']['first_name'] ?? 'User';
                    $text = $update['message']['text'] ?? '[Media/Non-text]';
                    $this->line("<comment>[" . date('H:i:s') . "]</comment> Pesan dari <info>{$user}</info>: {$text}");

                    try {
                        $handler->handleMessage($update['message']);
                    } catch (\Exception $e) {
                        $this->error("Error memproses pesan: " . $e->getMessage());
                    }
                }
            }

            if ($runOnce) {
                break;
            }

            usleep(500000); // 0.5s pause between polls
        }

        return Command::SUCCESS;
    }
}
