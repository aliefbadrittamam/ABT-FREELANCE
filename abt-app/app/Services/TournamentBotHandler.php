<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentParticipant;
use Illuminate\Support\Facades\Cache;

class TournamentBotHandler
{
    protected TournamentBotService $bot;

    public function __construct(TournamentBotService $bot)
    {
        $this->bot = $bot;
    }

    protected function getMainKeyboard(): array
    {
        return [
            'keyboard' => [
                [['text' => '🎮 Daftar Sesi Turnamen'], ['text' => '➕ Buka Sesi Baru']],
                [['text' => '📢 Salin Broadcast WA'], ['text' => '💡 Panduan']],
            ],
            'resize_keyboard' => true,
            'is_persistent' => true,
        ];
    }

    public function handleMessage(array $message): void
    {
        $chatId = (string)($message['chat']['id'] ?? '');
        $text = trim($message['text'] ?? '');
        $name = $message['from']['first_name'] ?? 'Admin';

        if (empty($chatId) || empty($text)) return;

        // Cancel
        if ($text === '❌ Batal' || $text === '/cancel') {
            Cache::forget("tg_tour_wiz_{$chatId}");
            $this->bot->sendMessage($chatId, "❌ Tindakan dibatalkan.", [
                'reply_markup' => json_encode($this->getMainKeyboard())
            ]);
            return;
        }

        // Active Wizard Step
        $session = Cache::get("tg_tour_wiz_{$chatId}");
        if ($session && isset($session['step'])) {
            $this->handleWizardStep($chatId, $text, $session);
            return;
        }

        // Main Commands
        if ($text === '🎮 Daftar Sesi Turnamen' || $text === '/sesi') {
            $this->showActiveSessions($chatId);
            return;
        }

        if ($text === '➕ Buka Sesi Baru' || $text === '/buat') {
            $this->startCreateSessionPrompt($chatId);
            return;
        }

        if ($text === '📢 Salin Broadcast WA' || $text === '/broadcast') {
            $this->showBroadcastChoice($chatId);
            return;
        }

        if ($text === '💡 Panduan' || str_starts_with($text, '/start') || str_starts_with($text, '/help')) {
            $welcome = "🎮 <b>Halo, {$name}! Selamat datang di Bot Turnamen eFootball Mobile</b>\n\n"
                     . "Gunakan tombol menu di bawah untuk mengelola sesi pertandingan, memantau slot tim, dan mengenerate teks broadcast WhatsApp secara instan.";
            $this->bot->sendMessage($chatId, $welcome, [
                'reply_markup' => json_encode($this->getMainKeyboard())
            ]);
            return;
        }

        // Unrecognized
        $this->bot->sendMessage($chatId, "Pilih salah satu menu di bawah:", [
            'reply_markup' => json_encode($this->getMainKeyboard())
        ]);
    }

    public function handleCallbackQuery(array $cb): void
    {
        $id = $cb['id'];
        $data = $cb['data'];
        $chatId = (string)($cb['message']['chat']['id'] ?? '');

        $this->bot->answerCallbackQuery($id);

        if ($data === 'tour_cancel') {
            Cache::forget("tg_tour_wiz_{$chatId}");
            $this->bot->sendMessage($chatId, "❌ Tindakan dibatalkan.", ['reply_markup' => json_encode($this->getMainKeyboard())]);
            return;
        }

        // Preset Creation (preset_5000, preset_10000, preset_15000, preset_20000)
        if (str_starts_with($data, 'preset_')) {
            $fee = (float)substr($data, 7);
            $prizeMap = [5000 => 30000, 10000 => 60000, 15000 => 95000, 20000 => 130000];
            $prize = $prizeMap[(int)$fee] ?? ($fee * 6);
            $feeK = ($fee >= 1000) ? ($fee / 1000) . 'K' : $fee;
            $prizeK = ($prize >= 1000) ? ($prize / 1000) . 'K' : $prize;

            $todayCount = Tournament::whereDate('created_at', now()->today())->count();
            $label = 'Sesi ' . ($todayCount + 1);

            $t = Tournament::create([
                'name' => "Turnamen {$feeK} Get {$prizeK}",
                'session_label' => $label,
                'entry_fee' => $fee,
                'prize_pool' => $prize,
                'max_slots' => 8,
                'admin_profit' => (8 * $fee) - $prize,
                'status' => 'open'
            ]);

            $this->bot->sendMessage($chatId, "✅ <b>Turnamen {$t->name} ({$t->session_label}) Berhasil Dibuat!</b>\n\nSlot tersedia: 8 Slot.\nTekan <b>🎮 Daftar Sesi Turnamen</b> untuk mulai memasukkan tim.");
            return;
        }

        // View specific session details
        if (str_starts_with($data, 'view_sesi_')) {
            $tId = (int)substr($data, 10);
            $t = Tournament::find($tId);
            if ($t) $this->showSessionCard($chatId, $t);
            return;
        }

        // Add team to slot
        if (str_starts_with($data, 'add_team_')) {
            $tId = (int)substr($data, 9);
            $t = Tournament::find($tId);
            if ($t && !$t->isFull()) {
                Cache::put("tg_tour_wiz_{$chatId}", [
                    'step' => 'awaiting_team_name',
                    'tournament_id' => $t->id
                ], now()->addMinutes(15));
                $this->bot->sendMessage($chatId, "✍️ Silakan ketik <b>Nama Tim</b> yang sudah transfer pendaftaran untuk {$t->name} ({$t->session_label}):");
            }
            return;
        }
    }

    protected function showActiveSessions(string $chatId): void
    {
        $sessions = Tournament::whereIn('status', ['open', 'full', 'ongoing'])->latest('id')->get();
        if ($sessions->isEmpty()) {
            $this->bot->sendMessage($chatId, "Belum ada sesi turnamen aktif. Tekan <b>➕ Buka Sesi Baru</b>.");
            return;
        }

        $buttons = [];
        foreach ($sessions as $s) {
            $label = "{$s->session_label} | {$s->name} ({$s->filled_slots_count}/{$s->max_slots})";
            $buttons[] = [['text' => $label, 'callback_data' => "view_sesi_{$s->id}"]];
        }
        $buttons[] = [['text' => '❌ Batal', 'callback_data' => 'tour_cancel']];

        $this->bot->sendMessage($chatId, "Pilih sesi untuk mengelola slot tim:", [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    protected function showSessionCard(string $chatId, Tournament $t): void
    {
        $broadcast = $t->generateBroadcastMessage();
        $text = "📋 <b>STATUS SLOT {$t->name} ({$t->session_label})</b>\n\n"
              . "<pre>{$broadcast}</pre>\n\n"
              . "Pilih aksi:";

        $buttons = [];
        if (!$t->isFull()) {
            $buttons[] = [['text' => '➕ Daftarkan Tim Baru', 'callback_data' => "add_team_{$t->id}"]];
        }
        $buttons[] = [['text' => '❌ Kembali', 'callback_data' => 'tour_cancel']];

        $this->bot->sendMessage($chatId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    protected function startCreateSessionPrompt(string $chatId): void
    {
        $buttons = [
            [
                ['text' => '5K Get 30K (Profit 10K)', 'callback_data' => 'preset_5000'],
                ['text' => '10K Get 60K (Profit 20K)', 'callback_data' => 'preset_10000'],
            ],
            [
                ['text' => '15K Get 95K (Profit 25K)', 'callback_data' => 'preset_15000'],
                ['text' => '20K Get 130K (Profit 30K)', 'callback_data' => 'preset_20000'],
            ],
            [
                ['text' => '❌ Batal', 'callback_data' => 'tour_cancel'],
            ]
        ];

        $this->bot->sendMessage($chatId, "⚡ <b>PILIH PRESET BIAYA & HADIAH SESI BARU:</b>", [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    protected function showBroadcastChoice(string $chatId): void
    {
        $sessions = Tournament::whereIn('status', ['open', 'full', 'ongoing'])->latest('id')->get();
        if ($sessions->isEmpty()) {
            $this->bot->sendMessage($chatId, "Tidak ada sesi aktif untuk di-broadcast.");
            return;
        }

        foreach ($sessions as $s) {
            $msg = $s->generateBroadcastMessage();
            $this->bot->sendMessage($chatId, "📢 <b>Format Broadcast Siap Salin ({$s->session_label}):</b>\n\n<code>" . htmlspecialchars($msg) . "</code>");
        }
    }

    protected function handleWizardStep(string $chatId, string $input, array $session): void
    {
        if ($session['step'] === 'awaiting_team_name') {
            $t = Tournament::find($session['tournament_id'] ?? 0);
            if ($t && !$t->isFull()) {
                // Find next available slot
                $used = $t->participants()->pluck('slot_number')->toArray();
                $target = 1;
                for ($i = 1; $i <= $t->max_slots; $i++) {
                    if (!in_array($i, $used)) { $target = $i; break; }
                }

                TournamentParticipant::create([
                    'tournament_id' => $t->id,
                    'slot_number' => $target,
                    'team_name' => trim($input)
                ]);

                if ($t->fresh()->isFull()) $t->update(['status' => 'full']);

                Cache::forget("tg_tour_wiz_{$chatId}");
                $this->bot->sendMessage($chatId, "✅ <b>Tim {$input}</b> berhasil dimasukkan ke <b>Slot #{$target}</b>!");
                $this->showSessionCard($chatId, $t->fresh());
            }
        }
    }
}
