# 05. PROMPT 5: BOT TELEGRAM KHUSUS TURNAMEN eFOOTBALL

Dokumen ini berisi arsitektur dan kode implementasi untuk **Bot Telegram Turnamen Terpisah** (`@abt_efootballTournament_bot`), lengkap dengan handler perintah, listener mandiri, dan menu interaktif pendaftaran slot dari HP.

---

## 📋 Instruksi Prompt 5

### 1. Konfigurasi Environment (`.env`)
Tambahkan variabel baru di file `.env` project:
```env
TELEGRAM_TOURNAMENT_BOT_TOKEN=token_bot_dari_botfather_anda
TELEGRAM_TOURNAMENT_ADMIN_ID=
```

---

### 2. Service Bot Turnamen: `app/Services/TournamentBotService.php`
Buat file service khusus untuk memanggil API Telegram dengan token bot turnamen:

```php
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
```

---

### 3. Handler Interaktif Bot Turnamen: `app/Services/TournamentBotHandler.php`

```php
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

        // Preset Creation (preset_5k, preset_10k, preset_15k, preset_20k)
        if (str_starts_with($data, 'preset_')) {
            $fee = (float)substr($data, 7);
            $prizeMap = [5000 => 30000, 10000 => 60000, 15000 => 95000, 20000 => 130000];
            $prize = $prizeMap[$fee] ?? ($fee * 6);
            $feeK = $fee / 1000 . 'K';
            $prizeK = $prize / 1000 . 'K';

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
                // Find next slot
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
```

---

### 4. Artisan Listener Command: `app/Console/Commands/TournamentListenCommand.php`
Jalankan di terminal tersendiri:
```bash
php artisan tournament:listen
```

Isi kode command:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TournamentBotService;
use App\Services\TournamentBotHandler;

class TournamentListenCommand extends Command
{
    protected $signature = 'tournament:listen';
    protected $description = 'Listen to incoming tournament bot messages (eFootball Mobile)';

    public function handle(TournamentBotService $service, TournamentBotHandler $handler)
    {
        if (!$service->isConfigured()) {
            $this->error('TELEGRAM_TOURNAMENT_BOT_TOKEN belum diset di .env.');
            return Command::FAILURE;
        }

        $this->info('🎮 Tournament Bot Listener AKTIF (@abt_efootballTournament_bot)...');
        $offset = 0;

        while (true) {
            $updates = $service->getUpdates($offset, 15);
            foreach ($updates as $u) {
                $offset = $u['update_id'] + 1;
                if (isset($u['message'])) $handler->handleMessage($u['message']);
                if (isset($u['callback_query'])) $handler->handleCallbackQuery($u['callback_query']);
            }
            usleep(500000);
        }
    }
}
```
