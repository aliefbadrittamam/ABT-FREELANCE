<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TournamentBotService;
use App\Services\TournamentBotHandler;

class TournamentListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:listen {--once : Run only one polling cycle and exit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to incoming tournament bot messages (eFootball Mobile)';

    /**
     * Execute the console command.
     */
    public function handle(TournamentBotService $service, TournamentBotHandler $handler)
    {
        if (!$service->isConfigured()) {
            $this->error('TELEGRAM_TOURNAMENT_BOT_TOKEN belum diset di .env.');
            return Command::FAILURE;
        }

        $this->info('🎮 Tournament Bot Listener AKTIF (@abt_efootballTournament_bot)...');
        $this->info('Tekan CTRL+C untuk menghentikan listener.');

        $offset = 0;
        $runOnce = $this->option('once');

        while (true) {
            $updates = $service->getUpdates($offset, 15);
            foreach ($updates as $u) {
                $offset = $u['update_id'] + 1;
                if (isset($u['message'])) {
                    $handler->handleMessage($u['message']);
                }
                if (isset($u['callback_query'])) {
                    $handler->handleCallbackQuery($u['callback_query']);
                }
            }

            if ($runOnce) {
                break;
            }

            usleep(500000);
        }

        return Command::SUCCESS;
    }
}
