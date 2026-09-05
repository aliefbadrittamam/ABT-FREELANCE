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
        if ($text === '❌ Batal' || $text === '/cancel' || $text === '❌ Batalkan') {
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
                     . "Gunakan tombol menu di bawah untuk mengelola sesi pertandingan, mengisi slot tim, memulai turnamen, menentukan juara 1, dan membuat broadcast WhatsApp secara instan.";
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

        // Preset Creation (preset_5000_8, preset_10000_8, etc.)
        if (str_starts_with($data, 'preset_')) {
            $parts = explode('_', $data);
            $fee = (float)($parts[1] ?? 5000);
            $slots = (int)($parts[2] ?? 8);

            $prizeMap = [5000 => 30000, 10000 => 60000, 15000 => 95000, 20000 => 130000];
            $prize = $slots === 4 ? ($fee * 4 * 0.75) : ($prizeMap[(int)$fee] ?? ($fee * 6));
            
            $feeK = ($fee >= 1000) ? ($fee / 1000) . 'K' : $fee;
            $prizeK = ($prize >= 1000) ? ($prize / 1000) . 'K' : $prize;

            $todayCount = Tournament::whereDate('created_at', now()->today())->count();
            $label = 'Sesi ' . ($todayCount + 1);

            $t = Tournament::create([
                'name' => "Turnamen {$feeK} Get {$prizeK}",
                'session_label' => $label,
                'entry_fee' => $fee,
                'prize_pool' => $prize,
                'max_slots' => $slots,
                'admin_profit' => ($slots * $fee) - $prize,
                'status' => 'open'
            ]);

            $this->bot->sendMessage($chatId, "✅ <b>Turnamen {$t->name} ({$t->session_label}) Berhasil Dibuat!</b>\n\nKapasitas: {$slots} Slot Tim.\nTekan tombol di bawah untuk mengelola:", [
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '📋 Buka Detail Sesi', 'callback_data' => "view_sesi_{$t->id}"]],
                    ]
                ])
            ]);
            return;
        }

        // View specific session details
        if (str_starts_with($data, 'view_sesi_')) {
            $tId = (int)substr($data, 10);
            $t = Tournament::find($tId);
            if ($t) $this->showSessionCard($chatId, $t);
            return;
        }

        // Add team to slot prompt
        if (str_starts_with($data, 'add_team_')) {
            $tId = (int)substr($data, 9);
            $t = Tournament::find($tId);
            if ($t && !$t->isFull()) {
                Cache::put("tg_tour_wiz_{$chatId}", [
                    'step' => 'awaiting_team_name',
                    'tournament_id' => $t->id
                ], now()->addMinutes(15));
                $this->bot->sendMessage($chatId, "✍️ Silakan ketik <b>Nama Tim</b> (+ No WA opsional, contoh: <code>GARUDA FC | 08123456789</code>) untuk mendaftar ke {$t->name} ({$t->session_label}):");
            }
            return;
        }

        // Start tournament (change status to ongoing)
        if (str_starts_with($data, 'start_tour_')) {
            $tId = (int)substr($data, 11);
            $t = Tournament::find($tId);
            if ($t) {
                if ($t->participants()->count() < 2) {
                    $this->bot->sendMessage($chatId, "⚠️ Minimal harus ada 2 tim terdaftar untuk dapat memulai turnamen!");
                    return;
                }
                $t->update(['status' => 'ongoing']);
                $this->bot->sendMessage($chatId, "🚀 <b>Turnamen {$t->name} ({$t->session_label}) Resmi DIMULAI!</b>\n\nStatus sekarang: <b>⚔️ Sedang Bertanding</b>.\nAnda sekarang dapat menentukan Juara 1 setelah laga usai.");
                $this->showSessionCard($chatId, $t->fresh());
            }
            return;
        }

        // Prompt to pick winner (list participants as buttons)
        if (str_starts_with($data, 'pick_winner_')) {
            $tId = (int)substr($data, 12);
            $t = Tournament::find($tId);
            if ($t) {
                if ($t->status !== 'ongoing') {
                    $this->bot->sendMessage($chatId, "⛔ <b>Turnamen belum dimulai!</b> Tekan tombol <b>🚀 Mulai Turnamen</b> terlebih dahulu sebelum dapat menetapkan pemenang.");
                    return;
                }

                $participants = $t->participants;
                if ($participants->isEmpty()) {
                    $this->bot->sendMessage($chatId, "Belum ada tim terdaftar di turnamen ini.");
                    return;
                }

                $buttons = [];
                foreach ($participants as $p) {
                    $buttons[] = [
                        ['text' => "👑 {$p->slot_number}. {$p->team_name}", 'callback_data' => "set_win_{$t->id}_{$p->id}"]
                    ];
                }
                $buttons[] = [['text' => '❌ Batal', 'callback_data' => "view_sesi_{$t->id}"]];

                $this->bot->sendMessage($chatId, "🏆 <b>PILIH TIM JUARA 1:</b>\nPilih tim pemenang yang berhak mendapatkan hadiah Rp " . number_format($t->prize_pool, 0, ',', '.') . ":", [
                    'reply_markup' => json_encode(['inline_keyboard' => $buttons])
                ]);
            }
            return;
        }

        // Execute set winner
        if (str_starts_with($data, 'set_win_')) {
            $parts = explode('_', $data);
            $tId = (int)($parts[2] ?? 0);
            $pId = (int)($parts[3] ?? 0);

            $t = Tournament::find($tId);
            $p = TournamentParticipant::find($pId);

            if ($t && $p && $p->tournament_id === $t->id) {
                $t->participants()->update(['is_winner' => false]);
                $p->update(['is_winner' => true]);
                $t->update(['winner_participant_id' => $p->id]);

                $prizeFormatted = 'Rp ' . number_format($t->prize_pool, 0, ',', '.');
                $waMsg = "Halo Tim *{$p->team_name}*, Selamat telah berhasil menjadi *JUARA 1* pada Turnamen eFootball Mobile ({$t->name} - {$t->session_label})!\n\n"
                       . "🎁 Hadiah sebesar *{$prizeFormatted}* akan segera kami transfer.\n"
                       . "Mohon kirimkan data rekening Bank / Nomor E-Wallet Anda (DANA/Gopay/OVO/ShopeePay). Terima kasih!";

                $text = "🎉 <b>JUARA 1 DITETAPKAN!</b>\n\n"
                      . "🏆 <b>Pemenang:</b> {$p->team_name}\n"
                      . "🎁 <b>Hadiah:</b> {$prizeFormatted}\n\n"
                      . "📲 <b>Template Chat WhatsApp ke Pemenang:</b>\n"
                      . "<code>" . htmlspecialchars($waMsg) . "</code>";

                $this->bot->sendMessage($chatId, $text);
                $this->showSessionCard($chatId, $t->fresh());
            }
            return;
        }

        // Complete tournament session
        if (str_starts_with($data, 'complete_tour_')) {
            $tId = (int)substr($data, 14);
            $t = Tournament::find($tId);
            if ($t) {
                $t->update(['status' => 'completed', 'completed_at' => now()]);
                $profitStr = 'Rp ' . number_format($t->admin_profit, 0, ',', '.');
                $this->bot->sendMessage($chatId, "✅ <b>Turnamen {$t->name} ({$t->session_label}) Telah Selesai!</b>\n\nLaba bersih admin: <b>{$profitStr}</b> berhasil dicatat ke sistem.");
                $this->showActiveSessions($chatId);
            }
            return;
        }

        // List teams to remove
        if (str_starts_with($data, 'remove_list_')) {
            $tId = (int)substr($data, 12);
            $t = Tournament::find($tId);
            if ($t) {
                $buttons = [];
                foreach ($t->participants as $p) {
                    $buttons[] = [
                        ['text' => "❌ Hapus Slot {$p->slot_number}: {$p->team_name}", 'callback_data' => "do_del_{$t->id}_{$p->id}"]
                    ];
                }
                $buttons[] = [['text' => '🔙 Kembali', 'callback_data' => "view_sesi_{$t->id}"]];

                $this->bot->sendMessage($chatId, "Pilih tim yang ingin dikeluarkan dari slot:", [
                    'reply_markup' => json_encode(['inline_keyboard' => $buttons])
                ]);
            }
            return;
        }

        // Execute delete participant
        if (str_starts_with($data, 'do_del_')) {
            $parts = explode('_', $data);
            $tId = (int)($parts[2] ?? 0);
            $pId = (int)($parts[3] ?? 0);
            $t = Tournament::find($tId);
            $p = TournamentParticipant::find($pId);

            if ($t && $p && $p->tournament_id === $t->id) {
                $team = $p->team_name;
                $slot = $p->slot_number;
                $p->delete();
                if ($t->status === 'full') $t->update(['status' => 'open']);

                $this->bot->sendMessage($chatId, "✅ Slot #{$slot} ({$team}) berhasil dikosongkan.");
                $this->showSessionCard($chatId, $t->fresh());
            }
            return;
        }
    }

    protected function showActiveSessions(string $chatId): void
    {
        $sessions = Tournament::whereIn('status', ['open', 'full', 'ongoing'])->latest('id')->get();
        if ($sessions->isEmpty()) {
            $this->bot->sendMessage($chatId, "Belum ada sesi turnamen aktif. Tekan tombol <b>➕ Buka Sesi Baru</b> di bawah.", [
                'reply_markup' => json_encode($this->getMainKeyboard())
            ]);
            return;
        }

        $buttons = [];
        foreach ($sessions as $s) {
            $icon = match($s->status) {
                'ongoing' => '⚔️',
                'full' => '🔒',
                default => '🟢'
            };
            $label = "{$icon} {$s->session_label} | {$s->name} ({$s->filled_slots_count}/{$s->max_slots})";
            $buttons[] = [['text' => $label, 'callback_data' => "view_sesi_{$s->id}"]];
        }
        $buttons[] = [['text' => '❌ Tutup Menu', 'callback_data' => 'tour_cancel']];

        $this->bot->sendMessage($chatId, "🎮 <b>DAFTAR SESI TURNAMEN AKTIF:</b>\nPilih sesi untuk mengelola:", [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    protected function showSessionCard(string $chatId, Tournament $t): void
    {
        $broadcast = $t->generateBroadcastMessage();
        $statusHuman = match($t->status) {
            'completed' => '✅ SELESAI',
            'ongoing' => '⚔️ SEDANG BERTANDING',
            'full' => '🔒 SLOT PENUH',
            default => '🟢 BUKA (PENDAFTARAN)'
        };

        $text = "📋 <b>KELOLA SESI: {$t->name} ({$t->session_label})</b>\n"
              . "Status: <b>{$statusHuman}</b>\n\n"
              . "<pre>{$broadcast}</pre>";

        $buttons = [];

        // 1. Add Team
        if (!$t->isFull() && $t->status !== 'completed') {
            $buttons[] = [['text' => '➕ Daftarkan Tim Baru', 'callback_data' => "add_team_{$t->id}"]];
        }

        // 2. Start Tournament Button (if not yet started)
        if (($t->status === 'open' || $t->status === 'full') && $t->filled_slots_count >= 2) {
            $buttons[] = [['text' => '🚀 Mulai Turnamen Sekarang', 'callback_data' => "start_tour_{$t->id}"]];
        }

        // 3. Winner Selection (Only active when ongoing)
        if ($t->status === 'ongoing') {
            $winnerLabel = $t->winner ? "🏆 Ganti Juara 1 ({$t->winner->team_name})" : "🏆 Pilih Juara 1";
            $buttons[] = [['text' => $winnerLabel, 'callback_data' => "pick_winner_{$t->id}"]];
        }

        // 4. Complete Session & Delete Team
        $managementRow = [];
        if ($t->participants()->count() > 0 && $t->status !== 'completed') {
            $managementRow[] = ['text' => '❌ Hapus Tim', 'callback_data' => "remove_list_{$t->id}"];
        }
        if ($t->status !== 'completed') {
            $managementRow[] = ['text' => '🏁 Selesaikan Sesi', 'callback_data' => "complete_tour_{$t->id}"];
        }
        if (!empty($managementRow)) {
            $buttons[] = $managementRow;
        }

        $buttons[] = [['text' => '🔙 Kembali ke Daftar Sesi', 'callback_data' => 'tour_cancel']];

        $this->bot->sendMessage($chatId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    protected function startCreateSessionPrompt(string $chatId): void
    {
        $buttons = [
            [
                ['text' => '5K Get 30K (8 Tim)', 'callback_data' => 'preset_5000_8'],
                ['text' => '10K Get 60K (8 Tim)', 'callback_data' => 'preset_10000_8'],
            ],
            [
                ['text' => '15K Get 95K (8 Tim)', 'callback_data' => 'preset_15000_8'],
                ['text' => '20K Get 130K (8 Tim)', 'callback_data' => 'preset_20000_8'],
            ],
            [
                ['text' => '⚡ 5K Mini (4 Tim)', 'callback_data' => 'preset_5000_4'],
                ['text' => '⚡ 10K Mini (4 Tim)', 'callback_data' => 'preset_10000_4'],
            ],
            [
                ['text' => '❌ Batal', 'callback_data' => 'tour_cancel'],
            ]
        ];

        $this->bot->sendMessage($chatId, "⚡ <b>PILIH PRESET BIAYA & HADIAH SESI BARU:</b>\n\nKlik salah satu paket untuk membuka sesi langsung:", [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    protected function showBroadcastChoice(string $chatId): void
    {
        $sessions = Tournament::whereIn('status', ['open', 'full', 'ongoing'])->latest('id')->get();
        if ($sessions->isEmpty()) {
            $this->bot->sendMessage($chatId, "Tidak ada sesi aktif untuk di-broadcast. Buka sesi baru terlebih dahulu.");
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
                // Support optional format: Team Name | WhatsApp
                $parts = array_map('trim', explode('|', $input));
                $teamName = $parts[0] ?? $input;
                $phone = $parts[1] ?? null;

                // Find next slot
                $used = $t->participants()->pluck('slot_number')->toArray();
                $target = 1;
                for ($i = 1; $i <= $t->max_slots; $i++) {
                    if (!in_array($i, $used)) { $target = $i; break; }
                }

                TournamentParticipant::create([
                    'tournament_id' => $t->id,
                    'slot_number' => $target,
                    'team_name' => $teamName,
                    'contact_wa' => $phone
                ]);

                if ($t->fresh()->isFull()) $t->update(['status' => 'full']);

                Cache::forget("tg_tour_wiz_{$chatId}");
                $this->bot->sendMessage($chatId, "✅ <b>Tim {$teamName}</b> berhasil dimasukkan ke <b>Slot #{$target}</b>!");
                $this->showSessionCard($chatId, $t->fresh());
            }
        }
    }
}
