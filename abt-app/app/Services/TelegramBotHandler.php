<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramBotHandler
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Get primary reply keyboard markup.
     */
    protected function getMainKeyboard(): array
    {
        return [
            'keyboard' => [
                [['text' => '📄 Buat Invoice Baru']],
                [['text' => '📊 Cek Status Invoice'], ['text' => '💡 Panduan & Bantuan']],
            ],
            'resize_keyboard' => true,
            'is_persistent' => true,
        ];
    }

    /**
     * Handle an incoming message update from Telegram.
     */
    public function handleMessage(array $message): void
    {
        $chatId = (string)($message['chat']['id'] ?? '');
        $text = trim($message['text'] ?? '');
        $userName = $message['from']['first_name'] ?? 'Admin';

        if (empty($chatId) || empty($text)) {
            return;
        }

        // 1. Check if user clicks Cancel
        if ($text === '❌ Batalkan' || $text === '/batal' || $text === '/cancel') {
            Cache::forget("tg_wizard_{$chatId}");
            $this->telegram->sendMessage($chatId, "❌ Proses pembuatan invoice telah dibatalkan.", [
                'reply_markup' => json_encode($this->getMainKeyboard()),
            ]);
            return;
        }

        // 2. Check active conversation wizard step
        $session = Cache::get("tg_wizard_{$chatId}");
        if ($session && isset($session['step'])) {
            $this->handleWizardStep($chatId, $text, $session);
            return;
        }

        // 3. Menu Button Triggers & Commands
        if ($text === '📄 Buat Invoice Baru' || $text === '/invoice' || $text === '/buat') {
            $this->startInvoiceWizard($chatId);
            return;
        }

        if ($text === '📊 Cek Status Invoice') {
            $this->telegram->sendMessage($chatId, "Ketik: <code>/status INV-...</code> untuk melihat status pembayaran invoice tertentu.");
            return;
        }

        if ($text === '💡 Panduan & Bantuan' || str_starts_with($text, '/start') || str_starts_with($text, '/help')) {
            $this->sendWelcomeMenu($chatId, $userName);
            return;
        }

        // Fallback for one-line quick command /inv
        if (str_starts_with($text, '/inv')) {
            $this->handleOneLineInvoice($chatId, $text);
            return;
        }

        if (str_starts_with($text, '/status')) {
            $this->handleInvoiceStatus($chatId, $text);
            return;
        }

        // Unrecognized: prompt with main keyboard
        $this->telegram->sendMessage($chatId, "Tekan tombol <b>📄 Buat Invoice Baru</b> di menu bawah untuk membuat invoice langkah demi langkah.", [
            'reply_markup' => json_encode($this->getMainKeyboard()),
        ]);
    }

    /**
     * Handle inline button click (callback query).
     */
    public function handleCallbackQuery(array $callbackQuery): void
    {
        $queryId = (string)($callbackQuery['id'] ?? '');
        $data = $callbackQuery['data'] ?? '';
        $chatId = (string)($callbackQuery['message']['chat']['id'] ?? '');

        if (empty($queryId) || empty($chatId)) {
            return;
        }

        $this->telegram->answerCallbackQuery($queryId);

        // Cancel callback
        if ($data === 'wizard_cancel') {
            Cache::forget("tg_wizard_{$chatId}");
            $this->telegram->sendMessage($chatId, "❌ Pembuatan invoice dibatalkan.", [
                'reply_markup' => json_encode($this->getMainKeyboard()),
            ]);
            return;
        }

        // Category selection callback
        if (str_starts_with($data, 'cat_')) {
            $categoryId = (int)substr($data, 4);
            $category = Category::find($categoryId);
            if ($category) {
                Cache::put("tg_wizard_{$chatId}", [
                    'step' => 'awaiting_client_name',
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                ], now()->addMinutes(30));

                $this->telegram->sendMessage(
                    $chatId,
                    "✅ Kategori dipilih: <b>{$category->name}</b>\n\n"
                    . "👤 <b>Langkah 1/4:</b>\nSilakan ketik <b>Nama Klien</b>:\n<i>(Contoh: Budi Santoso / Alyssa)</i>"
                );
            }
            return;
        }

        // Payment type callbacks
        $session = Cache::get("tg_wizard_{$chatId}");
        if (!$session) {
            $this->telegram->sendMessage($chatId, "Sesi telah berakhir. Silakan tekan tombol <b>📄 Buat Invoice Baru</b> kembali.", [
                'reply_markup' => json_encode($this->getMainKeyboard()),
            ]);
            return;
        }

        if ($data === 'pay_full') {
            $session['payment_type'] = 'full';
            $session['dp_amount'] = 0;
            $session['step'] = 'confirm';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));
            $this->showConfirmationPrompt($chatId, $session);
            return;
        }

        if ($data === 'pay_dp') {
            $total = (float)($session['total_amount'] ?? 0);
            $dp50 = round($total * 0.5);
            $dp40 = round($total * 0.4);
            $dp30 = round($total * 0.3);

            $session['payment_type'] = 'dp';
            $session['step'] = 'awaiting_dp_amount';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));

            $buttons = [
                [
                    ['text' => '50% (Rp ' . number_format($dp50, 0, ',', '.') . ')', 'callback_data' => "dp_val_{$dp50}"],
                    ['text' => '40% (Rp ' . number_format($dp40, 0, ',', '.') . ')', 'callback_data' => "dp_val_{$dp40}"],
                ],
                [
                    ['text' => '30% (Rp ' . number_format($dp30, 0, ',', '.') . ')', 'callback_data' => "dp_val_{$dp30}"],
                    ['text' => '✏️ Ketik Nominal DP Lain', 'callback_data' => 'dp_custom'],
                ],
                [
                    ['text' => '❌ Batalkan', 'callback_data' => 'wizard_cancel'],
                ]
            ];

            $this->telegram->sendMessage(
                $chatId,
                "💳 <b>Pilih Nominal Uang Muka (DP):</b>\nTotal Biaya: <b>Rp " . number_format($total, 0, ',', '.') . "</b>\n\nSilakan pilih salah satu preset di bawah atau ketik nominal sendiri:",
                ['reply_markup' => json_encode(['inline_keyboard' => $buttons])]
            );
            return;
        }

        if (str_starts_with($data, 'dp_val_')) {
            $dpAmount = (float)substr($data, 7);
            $session['payment_type'] = 'dp';
            $session['dp_amount'] = $dpAmount;
            $session['step'] = 'confirm';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));
            $this->showConfirmationPrompt($chatId, $session);
            return;
        }

        if ($data === 'dp_custom') {
            $session['payment_type'] = 'dp';
            $session['step'] = 'awaiting_custom_dp';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));
            $this->telegram->sendMessage($chatId, "Ketik nominal DP yang Anda inginkan (angkanya saja, contoh: <code>100000</code>):");
            return;
        }

        // Final confirmation callback
        if ($data === 'confirm_create') {
            $this->executeInvoiceCreation($chatId, $session);
            Cache::forget("tg_wizard_{$chatId}");
            return;
        }
    }

    /**
     * Send welcome message with persistent keyboard buttons.
     */
    protected function sendWelcomeMenu(string $chatId, string $userName): void
    {
        $text = "⚡ <b>Halo, {$userName}! Selamat datang di Asisten Invoice ABT-FREELANCE</b>\n\n"
              . "Anda dapat membuat invoice profesional, memantau pembayaran, dan membagikan link portal klien langsung dari sini.\n\n"
              . "👇 <b>Tekan tombol di bawah untuk memulai:</b>";

        $this->telegram->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($this->getMainKeyboard()),
        ]);
    }

    /**
     * Start the step-by-step invoice creation wizard.
     */
    protected function startInvoiceWizard(string $chatId): void
    {
        $categories = Category::all();

        $buttons = [];
        $row = [];
        foreach ($categories as $cat) {
            $icon = match(strtoupper($cat->prefix)) {
                'JOKI' => '📚',
                'WEB' => '💻',
                'DESAIN' => '🎨',
                default => '💼'
            };
            $row[] = ['text' => "{$icon} {$cat->name}", 'callback_data' => "cat_{$cat->id}"];
            if (count($row) === 2) {
                $buttons[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $buttons[] = $row;
        }
        $buttons[] = [['text' => '❌ Batalkan', 'callback_data' => 'wizard_cancel']];

        Cache::put("tg_wizard_{$chatId}", ['step' => 'awaiting_category'], now()->addMinutes(30));

        $this->telegram->sendMessage(
            $chatId,
            "📄 <b>BUAT INVOICE BARU</b>\n\nSilakan pilih <b>Kategori Jasa</b>:",
            ['reply_markup' => json_encode(['inline_keyboard' => $buttons])]
        );
    }

    /**
     * Handle textual input during active conversation wizard steps.
     */
    protected function handleWizardStep(string $chatId, string $input, array $session): void
    {
        $step = $session['step'];

        // Step 1: Client Name
        if ($step === 'awaiting_client_name') {
            $session['client_name'] = $input;
            $session['step'] = 'awaiting_title';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));

            $this->telegram->sendMessage(
                $chatId,
                "✅ Klien: <b>{$input}</b>\n\n"
                . "📋 <b>Langkah 2/4:</b>\nSilakan ketik <b>Judul Tugas / Proyek</b>:\n<i>(Contoh: Skripsi Bab 4 dan 5 / Pembuatan Website Portofolio)</i>"
            );
            return;
        }

        // Step 2: Task Title
        if ($step === 'awaiting_title') {
            $session['title'] = $input;
            $session['step'] = 'awaiting_total_amount';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));

            $this->telegram->sendMessage(
                $chatId,
                "✅ Proyek: <b>{$input}</b>\n\n"
                . "💰 <b>Langkah 3/4:</b>\nBerapa <b>Total Biaya Proyek</b>?\n<i>(Ketik angka saja, contoh: <code>250000</code> atau <code>1500000</code>)</i>"
            );
            return;
        }

        // Step 3: Total Amount
        if ($step === 'awaiting_total_amount') {
            $cleanAmount = (float)preg_replace('/[^0-9]/', '', $input);
            if ($cleanAmount <= 0) {
                $this->telegram->sendMessage($chatId, "⚠️ Nominal tidak valid. Masukkan nominal angka saja (contoh: <code>250000</code>):");
                return;
            }

            $session['total_amount'] = $cleanAmount;
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));

            $buttons = [
                [
                    ['text' => '💵 Bayar Lunas Langsung', 'callback_data' => 'pay_full'],
                    ['text' => '💳 Dengan DP (Bertahap)', 'callback_data' => 'pay_dp'],
                ],
                [
                    ['text' => '❌ Batalkan', 'callback_data' => 'wizard_cancel'],
                ]
            ];

            $formatted = 'Rp ' . number_format($cleanAmount, 0, ',', '.');
            $this->telegram->sendMessage(
                $chatId,
                "✅ Total Biaya: <b>{$formatted}</b>\n\n"
                . "💳 <b>Langkah 4/4:</b>\nPilih <b>Metode Pembayaran</b>:",
                ['reply_markup' => json_encode(['inline_keyboard' => $buttons])]
            );
            return;
        }

        // Step 4b: Custom DP
        if ($step === 'awaiting_custom_dp' || $step === 'awaiting_dp_amount') {
            $cleanDp = (float)preg_replace('/[^0-9]/', '', $input);
            if ($cleanDp <= 0 || $cleanDp >= $session['total_amount']) {
                $this->telegram->sendMessage($chatId, "⚠️ Nominal DP harus lebih kecil dari total biaya (Rp " . number_format($session['total_amount'], 0, ',', '.') . "). Ketik ulang:");
                return;
            }

            $session['payment_type'] = 'dp';
            $session['dp_amount'] = $cleanDp;
            $session['step'] = 'confirm';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));
            $this->showConfirmationPrompt($chatId, $session);
            return;
        }
    }

    /**
     * Show preview confirmation prompt before final rendering.
     */
    protected function showConfirmationPrompt(string $chatId, array $session): void
    {
        $category = Category::find($session['category_id'] ?? 1);
        $total = (float)($session['total_amount'] ?? 0);
        $isDp = ($session['payment_type'] ?? '') === 'dp';
        $dp = (float)($session['dp_amount'] ?? 0);
        $sisa = max(0, $total - $dp);

        $previewNumber = Invoice::generateInvoiceNumber($category?->id);

        $text = "📋 <b>RINGKASAN INVOICE BARU</b>\n"
              . "────────────────────────\n"
              . "• <b>No. Invoice:</b> <code>{$previewNumber}</code>\n"
              . "• <b>Kategori:</b> {$category->name}\n"
              . "• <b>Nama Klien:</b> {$session['client_name']}\n"
              . "• <b>Judul Tugas:</b> {$session['title']}\n"
              . "• <b>Total Biaya:</b> Rp " . number_format($total, 0, ',', '.') . "\n"
              . ($isDp 
                  ? "• <b>Metode:</b> Bertahap (DP)\n  - Wajib DP: <b>Rp " . number_format($dp, 0, ',', '.') . "</b>\n  - Sisa Pelunasan: Rp " . number_format($sisa, 0, ',', '.') . "\n"
                  : "• <b>Metode:</b> Bayar Lunas Langsung\n")
              . "────────────────────────\n"
              . "Apakah data di atas sudah sesuai?";

        $buttons = [
            [
                ['text' => '✅ Buat & Render Invoice', 'callback_data' => 'confirm_create'],
            ],
            [
                ['text' => '❌ Batalkan', 'callback_data' => 'wizard_cancel'],
            ]
        ];

        $this->telegram->sendMessage($chatId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    /**
     * Execute invoice creation in database and send rendered HD image.
     */
    protected function executeInvoiceCreation(string $chatId, array $session): void
    {
        $this->telegram->sendMessage($chatId, "⏳ <i>Sedang membuat invoice dan merender gambar resmi HD...</i>");

        try {
            $category = Category::find($session['category_id'] ?? 1) ?: Category::first();
            $invoiceNumber = Invoice::generateInvoiceNumber($category->id);
            $isDp = ($session['payment_type'] ?? 'full') === 'dp';
            $dp = (float)($session['dp_amount'] ?? 0);
            $total = (float)$session['total_amount'];

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'title' => $session['title'],
                'client_name' => $session['client_name'],
                'category_id' => $category->id,
                'description' => "Pengerjaan {$session['title']} untuk {$session['client_name']}. Sesuai kesepakatan.",
                'deadline' => now()->addDays(3),
                'payment_type' => $isDp ? 'dp' : 'full',
                'dp_amount' => $isDp ? $dp : null,
                'total_amount' => $total,
                'status' => 'unpaid',
            ]);

            // Render HD PNG invoice via Puppeteer
            $exportDir = storage_path('app/public/invoices/exports');
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $htmlContent = view('invoices.standalone', compact('invoice'))->render();
            $tempHtmlPath = storage_path('app/public/invoices/exports/tg_temp_' . $invoice->id . '_' . time() . '.html');
            file_put_contents($tempHtmlPath, $htmlContent);

            $pngFilename = $invoice->invoice_number . '.png';
            $pngPath = $exportDir . '/' . $pngFilename;
            $scriptPath = base_path('render_image.mjs');

            $command = "node \"{$scriptPath}\" \"{$tempHtmlPath}\" \"{$pngPath}\" 2>&1";
            exec($command, $output, $returnCode);
            @unlink($tempHtmlPath);

            $clientUrl = $invoice->getClientViewUrl();
            $formattedTotal = 'Rp ' . number_format($total, 0, ',', '.');
            $formattedDp = $dp > 0 ? 'Rp ' . number_format($dp, 0, ',', '.') : '-';
            $sisa = $isDp ? 'Rp ' . number_format(max(0, $total - $dp), 0, ',', '.') : 'Rp 0';

            // WhatsApp Share Text
            $brand = $category->brand_name ?: 'ABT-FREELANCE';
            $waMessage = "Halo {$session['client_name']}, berikut Invoice resmi dari *{$brand}*:\n\n"
                       . "📄 *Nomor:* {$invoice->invoice_number}\n"
                       . "📋 *Proyek:* {$session['title']}\n"
                       . "💰 *Total Biaya:* {$formattedTotal}\n"
                       . ($isDp ? "💵 *Tagihan DP:* {$formattedDp}\n" : "")
                       . "🔗 *Lihat Invoice & QRIS:* {$clientUrl}\n\n"
                       . "Mohon konfirmasi setelah transfer pembayaran ya. Terima kasih 🙏";

            $caption = "✅ <b>INVOICE RESMI BERHASIL DIBUAT!</b>\n\n"
                     . "📄 <b>Nomor:</b> <code>{$invoice->invoice_number}</code>\n"
                     . "👤 <b>Klien:</b> {$session['client_name']}\n"
                     . "📋 <b>Proyek:</b> {$session['title']}\n"
                     . "🏷️ <b>Kategori:</b> {$category->name}\n"
                     . "💰 <b>Total Biaya:</b> {$formattedTotal}\n"
                     . ($isDp ? "💳 <b>Wajib DP:</b> {$formattedDp} (Sisa: {$sisa})\n" : "💳 <b>Metode:</b> Bayar Lunas Langsung\n")
                     . "🌐 <b>Link Portal Klien:</b>\n{$clientUrl}\n\n"
                     . "📲 <b>Format Chat WhatsApp (Tinggal Salin):</b>\n"
                     . "<code>" . htmlspecialchars($waMessage) . "</code>";

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '🌐 Buka Portal Klien', 'url' => $clientUrl],
                        ['text' => '💬 Buka di Web Admin', 'url' => config('app.url') . "/invoices/{$invoice->id}"],
                    ]
                ]
            ];

            if (file_exists($pngPath) && filesize($pngPath) > 0) {
                $this->telegram->sendPhotoToChat($chatId, $pngPath, $caption, [
                    'reply_markup' => json_encode($replyMarkup)
                ]);
            } else {
                $this->telegram->sendMessage($chatId, $caption, [
                    'reply_markup' => json_encode($replyMarkup)
                ]);
            }

            // Remind with main menu keyboard
            $this->telegram->sendMessage($chatId, "✨ Selesai! Anda dapat membuat invoice lagi kapan saja menggunakan tombol di bawah.", [
                'reply_markup' => json_encode($this->getMainKeyboard())
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram executeInvoiceCreation failed', ['error' => $e->getMessage()]);
            $this->telegram->sendMessage($chatId, "❌ <b>Gagal membuat invoice:</b> " . $e->getMessage(), [
                'reply_markup' => json_encode($this->getMainKeyboard())
            ]);
        }
    }

    /**
     * Check invoice status.
     */
    protected function handleInvoiceStatus(string $chatId, string $rawText): void
    {
        $query = trim(substr($rawText, 7));
        if (empty($query)) {
            $this->telegram->sendMessage($chatId, "Gunakan: <code>/status INV-...</code> atau ketik ID/nomornya.");
            return;
        }

        $invoice = Invoice::where('invoice_number', 'like', "%{$query}%")->first();
        if (!$invoice) {
            $this->telegram->sendMessage($chatId, "❌ Invoice tidak ditemukan.");
            return;
        }

        $statusText = match ($invoice->status) {
            'paid' => '✅ LUNAS',
            'dp_paid' => '💳 DP TERBAYAR',
            'canceled' => '❌ DIBATALKAN',
            default => '⏳ BELUM BAYAR'
        };

        $msg = "📄 <b>Detail Invoice {$invoice->invoice_number}</b>\n\n"
             . "👤 Klien: {$invoice->client_name}\n"
             . "📋 Proyek: {$invoice->title}\n"
             . "💰 Total: Rp " . number_format($invoice->total_amount, 0, ',', '.') . "\n"
             . "📊 Status: <b>{$statusText}</b>\n"
             . "🔗 Link: {$invoice->getClientViewUrl()}";

        $this->telegram->sendMessage($chatId, $msg);
    }

    /**
     * Fallback quick single-line command (/inv title | client | total | dp).
     */
    protected function handleOneLineInvoice(string $chatId, string $rawText): void
    {
        $content = trim(substr($rawText, 4));
        if (empty($content)) {
            $this->startInvoiceWizard($chatId);
            return;
        }

        $parts = array_map('trim', explode('|', $content));
        $session = [
            'title' => $parts[0] ?? 'Tugas Baru',
            'client_name' => $parts[1] ?? 'Klien',
            'total_amount' => (float)preg_replace('/[^0-9]/', '', $parts[2] ?? '0'),
            'dp_amount' => isset($parts[3]) ? (float)preg_replace('/[^0-9]/', '', $parts[3]) : 0,
            'payment_type' => (isset($parts[3]) && (float)preg_replace('/[^0-9]/', '', $parts[3]) > 0) ? 'dp' : 'full',
            'category_id' => 1,
        ];

        if ($session['total_amount'] <= 0) {
            $this->telegram->sendMessage($chatId, "❌ Total biaya tidak valid. Silakan gunakan tombol <b>📄 Buat Invoice Baru</b>.", [
                'reply_markup' => json_encode($this->getMainKeyboard())
            ]);
            return;
        }

        $this->executeInvoiceCreation($chatId, $session);
    }
}
