<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Category;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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
            $this->telegram->sendMessage($chatId, "❌ Proses telah dibatalkan.", [
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

        // 3. Check if user directly typed invoice sequence number (e.g. "86", "#86", "inv 86", "inv-86")
        if (preg_match('/^(?:inv[\s\-]*)?#?(\d+)$/i', $text, $match)) {
            $seqNumber = (int)$match[1];
            $this->findAndShowInvoiceByNumber($chatId, $seqNumber);
            return;
        }

        // 4. Menu Button Triggers & Commands
        if ($text === '📄 Buat Invoice Baru' || $text === '/invoice' || $text === '/buat') {
            $this->startInvoiceWizard($chatId);
            return;
        }

        if ($text === '📊 Cek Status Invoice') {
            $this->showStatusMenu($chatId);
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
        $this->telegram->sendMessage($chatId, "Ketik nomor invoice (contoh: <code>86</code> atau <code>81</code>) untuk cek/update status, atau tekan tombol menu di bawah:", [
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
            $this->telegram->sendMessage($chatId, "❌ Tindakan dibatalkan.", [
                'reply_markup' => json_encode($this->getMainKeyboard()),
            ]);
            return;
        }

        // Status Management: View Specific Invoice Details
        if (str_starts_with($data, 'view_inv_')) {
            $invId = (int)substr($data, 9);
            $invoice = Invoice::find($invId);
            if ($invoice) {
                $this->showInvoiceDetailCard($chatId, $invoice);
            }
            return;
        }

        // Status Management: Update Invoice Status (paid, dp_paid, unpaid, canceled)
        if (str_starts_with($data, 'set_status_')) {
            $this->handleStatusUpdateCallback($chatId, $data);
            return;
        }

        // On-demand Send PDF
        if (str_starts_with($data, 'send_pdf_')) {
            $invId = (int)substr($data, 9);
            $invoice = Invoice::find($invId);
            if ($invoice) {
                $this->telegram->sendMessage($chatId, "⏳ <i>Sedang menyiapkan dokumen PDF resmi {$invoice->invoice_number}...</i>");
                $pdfPath = $this->renderPdf($invoice);
                if ($pdfPath && file_exists($pdfPath)) {
                    $this->telegram->sendDocumentToChat($chatId, $pdfPath, "📄 Dokumen Resmi PDF: <b>{$invoice->invoice_number}</b>");
                } else {
                    $this->telegram->sendMessage($chatId, "❌ Gagal membuat file PDF. Silakan coba buka via link: {$invoice->getClientViewUrl()}");
                }
            }
            return;
        }

        // On-demand Send PNG
        if (str_starts_with($data, 'send_png_')) {
            $invId = (int)substr($data, 9);
            $invoice = Invoice::find($invId);
            if ($invoice) {
                $this->telegram->sendMessage($chatId, "⏳ <i>Sedang menyiapkan gambar PNG {$invoice->invoice_number}...</i>");
                $pngPath = $this->renderPng($invoice);
                if ($pngPath && file_exists($pngPath)) {
                    $this->telegram->sendPhotoToChat($chatId, $pngPath, "🖼️ Gambar Invoice HD: <b>{$invoice->invoice_number}</b>");
                } else {
                    $this->telegram->sendMessage($chatId, "❌ Gagal membuat gambar PNG.");
                }
            }
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
                    . "👤 <b>Langkah 1/5:</b>\nSilakan ketik <b>Nama Klien</b>:\n<i>(Contoh: Budi Santoso / Alyssa)</i>"
                );
            }
            return;
        }

        $session = Cache::get("tg_wizard_{$chatId}");
        if (!$session) {
            $this->telegram->sendMessage($chatId, "Sesi telah berakhir. Silakan tekan tombol <b>📄 Buat Invoice Baru</b> kembali.", [
                'reply_markup' => json_encode($this->getMainKeyboard()),
            ]);
            return;
        }

        // Deadline callbacks
        if (str_starts_with($data, 'dl_')) {
            $deadlineOption = substr($data, 3);
            $deadlineDate = match($deadlineOption) {
                'today' => Carbon::now()->endOfDay(),
                'tomorrow' => Carbon::tomorrow()->endOfDay(),
                '3days' => Carbon::now()->addDays(3)->endOfDay(),
                '7days' => Carbon::now()->addDays(7)->endOfDay(),
                default => Carbon::now()->addDays(3)->endOfDay()
            };

            $session['deadline'] = $deadlineDate->toDateTimeString();
            $session['step'] = 'awaiting_total_amount';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));

            $formattedDl = $deadlineDate->translatedFormat('d F Y, H:i') . ' WIB';
            $this->telegram->sendMessage(
                $chatId,
                "✅ Deadline diset: <b>{$formattedDl}</b>\n\n"
                . "💰 <b>Langkah 4/5:</b>\nBerapa <b>Total Biaya Proyek</b>?\n<i>(Ketik angka saja, contoh: <code>250000</code> atau <code>1500000</code>)</i>"
            );
            return;
        }

        if ($data === 'dl_custom') {
            $session['step'] = 'awaiting_custom_deadline';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));
            $this->telegram->sendMessage($chatId, "Ketik batas deadline yang Anda inginkan:\n<i>(Contoh: <code>10 Sep 2026</code> atau <code>10-09-2026 21:00</code>)</i>");
            return;
        }

        // Payment type callbacks
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

        // Final creation callbacks with format choice
        if ($data === 'confirm_png') {
            $this->executeInvoiceCreation($chatId, $session, 'png');
            Cache::forget("tg_wizard_{$chatId}");
            return;
        }

        if ($data === 'confirm_pdf') {
            $this->executeInvoiceCreation($chatId, $session, 'pdf');
            Cache::forget("tg_wizard_{$chatId}");
            return;
        }

        if ($data === 'confirm_both') {
            $this->executeInvoiceCreation($chatId, $session, 'both');
            Cache::forget("tg_wizard_{$chatId}");
            return;
        }
    }

    /**
     * Find invoice by sequence number (e.g. typing 86 finds INV-JOKI-086-...).
     */
    protected function findAndShowInvoiceByNumber(string $chatId, int $seqNumber): void
    {
        $padded = str_pad($seqNumber, 3, '0', STR_PAD_LEFT);
        
        // Search by sequence pattern or raw id/number
        $invoice = Invoice::where('invoice_number', 'LIKE', "%-{$padded}-%")
            ->orWhere('invoice_number', 'LIKE', "%-{$seqNumber}-%")
            ->orWhere('invoice_number', 'LIKE', "%-{$padded}")
            ->orWhere('id', $seqNumber)
            ->latest('id')
            ->first();

        if (!$invoice) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ Invoice dengan nomor urut <b>#{$seqNumber}</b> tidak ditemukan.\n\n"
                . "💡 <i>Ketik angka nomor urut lainnya (contoh: 81) atau tekan menu Cek Status.</i>",
                ['reply_markup' => json_encode($this->getMainKeyboard())]
            );
            return;
        }

        $this->showInvoiceDetailCard($chatId, $invoice);
    }

    /**
     * Show interactive status menu with recent active invoices.
     */
    protected function showStatusMenu(string $chatId): void
    {
        // Fetch up to 6 most recent active/pending invoices
        $activeInvoices = Invoice::where('status', '!=', 'canceled')
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        $buttons = [];
        foreach ($activeInvoices as $inv) {
            $icon = match($inv->status) {
                'paid' => '✅',
                'dp_paid' => '💳',
                default => '⏳'
            };

            // Extract short number
            $parts = explode('-', $inv->invoice_number);
            $shortNum = isset($parts[2]) ? "#" . ltrim($parts[2], '0') : "#{$inv->id}";
            $label = "{$icon} {$shortNum} - " . substr($inv->client_name, 0, 12) . " (" . ($inv->status === 'paid' ? 'Lunas' : ($inv->status === 'dp_paid' ? 'DP' : 'Belum Bayar')) . ")";

            $buttons[] = [
                ['text' => $label, 'callback_data' => "view_inv_{$inv->id}"]
            ];
        }

        $buttons[] = [
            ['text' => '❌ Tutup Menu', 'callback_data' => 'wizard_cancel']
        ];

        $text = "📊 <b>KELOLA STATUS PEMBAYARAN INVOICE</b>\n\n"
              . "Pilih invoice dari daftar di bawah untuk melihat/mengubah status pembayarannya:\n\n"
              . "💡 <i>Atau Anda bisa langsung mengetik angka nomor invoice (contoh: <code>86</code> atau <code>81</code>).</i>";

        $this->telegram->sendMessage($chatId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    /**
     * Show detailed card for an invoice with status action buttons.
     */
    protected function showInvoiceDetailCard(string $chatId, Invoice $invoice): void
    {
        $statusText = match ($invoice->status) {
            'paid' => '✅ LUNAS',
            'dp_paid' => '💳 DP TERBAYAR',
            'canceled' => '❌ DIBATALKAN',
            default => '⏳ BELUM BAYAR'
        };

        $totalFormatted = 'Rp ' . number_format($invoice->total_amount, 0, ',', '.');
        $dpFormatted = $invoice->dp_amount ? 'Rp ' . number_format($invoice->dp_amount, 0, ',', '.') : '-';
        $sisaFormatted = $invoice->remaining_amount ? 'Rp ' . number_format($invoice->remaining_amount, 0, ',', '.') : 'Rp 0';
        $deadlineText = $invoice->deadline ? $invoice->deadline->translatedFormat('d M Y, H:i') . ' WIB' : '-';

        $text = "📄 <b>DETAIL INVOICE:</b> <code>{$invoice->invoice_number}</code>\n"
              . "────────────────────────\n"
              . "• <b>Klien:</b> {$invoice->client_name}\n"
              . "• <b>Proyek:</b> {$invoice->title}\n"
              . "• <b>Deadline:</b> {$deadlineText}\n"
              . "• <b>Total Biaya:</b> {$totalFormatted}\n"
              . ($invoice->payment_type === 'dp'
                  ? "• <b>Uang Muka (DP):</b> {$dpFormatted}\n• <b>Sisa Pelunasan:</b> {$sisaFormatted}\n"
                  : "• <b>Metode:</b> Bayar Lunas Langsung\n")
              . "• <b>Status Saat Ini:</b> <b>{$statusText}</b>\n"
              . "────────────────────────\n"
              . "🌐 <b>Link Portal Klien:</b>\n{$invoice->getClientViewUrl()}\n\n"
              . "👇 <b>Pilih tombol di bawah untuk mengubah status pembayaran atau meminta file:</b>";

        // Dynamic Action Buttons according to current status
        $buttons = [];

        if ($invoice->status === 'unpaid') {
            $buttons[] = [
                ['text' => '💳 Tandai DP Terbayar', 'callback_data' => "set_status_dp_paid_{$invoice->id}"],
                ['text' => '✅ Tandai Lunas', 'callback_data' => "set_status_paid_{$invoice->id}"],
            ];
            $buttons[] = [
                ['text' => '❌ Batalkan Tagihan', 'callback_data' => "set_status_canceled_{$invoice->id}"],
            ];
        } elseif ($invoice->status === 'dp_paid') {
            $buttons[] = [
                ['text' => '✅ Tandai Pelunasan Selesai (LUNAS)', 'callback_data' => "set_status_paid_{$invoice->id}"],
            ];
            $buttons[] = [
                ['text' => '⏳ Ubah ke Belum Bayar', 'callback_data' => "set_status_unpaid_{$invoice->id}"],
                ['text' => '❌ Batalkan Tagihan', 'callback_data' => "set_status_canceled_{$invoice->id}"],
            ];
        } elseif ($invoice->status === 'paid') {
            $buttons[] = [
                ['text' => '⏳ Ubah ke Belum Bayar', 'callback_data' => "set_status_unpaid_{$invoice->id}"],
                ['text' => '💳 Ubah ke DP Terbayar', 'callback_data' => "set_status_dp_paid_{$invoice->id}"],
            ];
        } else {
            // canceled
            $buttons[] = [
                ['text' => '♻️ Aktifkan Kembali (Belum Bayar)', 'callback_data' => "set_status_unpaid_{$invoice->id}"],
            ];
        }

        // File download buttons
        $buttons[] = [
            ['text' => '📄 Minta File PDF', 'callback_data' => "send_pdf_{$invoice->id}"],
            ['text' => '🖼️ Minta File PNG', 'callback_data' => "send_png_{$invoice->id}"],
        ];

        $buttons[] = [
            ['text' => '🔙 Kembali ke Menu', 'callback_data' => 'wizard_cancel'],
        ];

        $this->telegram->sendMessage($chatId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
    }

    /**
     * Handle status change button callback (set_status_{status}_{id}).
     */
    protected function handleStatusUpdateCallback(string $chatId, string $data): void
    {
        // Example: set_status_paid_85 or set_status_dp_paid_85
        $parts = explode('_', $data);
        // data format: set_status_{status}_{id} or set_status_dp_paid_{id}
        $invId = (int)end($parts);
        $invoice = Invoice::find($invId);

        if (!$invoice) {
            $this->telegram->sendMessage($chatId, "❌ Invoice tidak ditemukan.");
            return;
        }

        $newStatus = 'unpaid';
        if (str_contains($data, 'set_status_paid_')) {
            $newStatus = 'paid';
        } elseif (str_contains($data, 'set_status_dp_paid_')) {
            $newStatus = 'dp_paid';
        } elseif (str_contains($data, 'set_status_canceled_')) {
            $newStatus = 'canceled';
        } elseif (str_contains($data, 'set_status_unpaid_')) {
            $newStatus = 'unpaid';
        }

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'paid') {
            $updateData['paid_at'] = now();
            if ($invoice->payment_type === 'dp' && empty($invoice->dp_paid_at)) {
                $updateData['dp_paid_at'] = now();
            }
        } elseif ($newStatus === 'dp_paid') {
            $updateData['dp_paid_at'] = $invoice->dp_paid_at ?: now();
            $updateData['paid_at'] = null;
        } elseif ($newStatus === 'unpaid' || $newStatus === 'canceled') {
            $updateData['paid_at'] = null;
            $updateData['dp_paid_at'] = null;
        }

        $invoice->update($updateData);

        $statusHuman = match($newStatus) {
            'paid' => '✅ LUNAS',
            'dp_paid' => '💳 DP TERBAYAR',
            'canceled' => '❌ DIBATALKAN',
            default => '⏳ BELUM BAYAR'
        };

        // WhatsApp Confirmation text generator
        $brand = $invoice->category->brand_name ?: 'ABT-FREELANCE';
        $waMsg = "";
        if ($newStatus === 'paid') {
            $waMsg = "Halo {$invoice->client_name}, pembayaran untuk Invoice *{$invoice->invoice_number}* telah kami terima dan terverifikasi *LUNAS*. Terima kasih atas kepercayaannya! 🙏";
        } elseif ($newStatus === 'dp_paid') {
            $dpStr = 'Rp ' . number_format($invoice->dp_amount, 0, ',', '.');
            $waMsg = "Halo {$invoice->client_name}, pembayaran uang muka (DP) sebesar *{$dpStr}* untuk Invoice *{$invoice->invoice_number}* telah terverifikasi. Tugas segera kami proses. Terima kasih! 🙏";
        }

        $notificationText = "🎉 <b>STATUS BERHASIL DIUPDATE!</b>\n\n"
                          . "📄 <b>Invoice:</b> <code>{$invoice->invoice_number}</code>\n"
                          . "👤 <b>Klien:</b> {$invoice->client_name}\n"
                          . "📊 <b>Status Baru:</b> <b>{$statusHuman}</b>\n\n"
                          . "🌐 <b>Status di Portal Klien & Dashboard Otomatis Tersinkron!</b>";

        if ($waMsg) {
            $notificationText .= "\n\n📲 <b>Pesan Konfirmasi WhatsApp (Tinggal Salin):</b>\n"
                               . "<code>" . htmlspecialchars($waMsg) . "</code>";
        }

        $this->telegram->sendMessage($chatId, $notificationText);

        // Show refreshed detail card with updated buttons
        $this->showInvoiceDetailCard($chatId, $invoice->fresh());
    }

    /**
     * Send welcome message with persistent keyboard buttons.
     */
    protected function sendWelcomeMenu(string $chatId, string $userName): void
    {
        $text = "⚡ <b>Halo, {$userName}! Selamat datang di Asisten Invoice ABT-FREELANCE</b>\n\n"
              . "Anda dapat membuat invoice profesional, mengecek status cukup dengan mengetik nomor (contoh: <code>86</code>), dan mengunduh PDF/PNG langsung dari sini.\n\n"
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
                . "📋 <b>Langkah 2/5:</b>\nSilakan ketik <b>Judul Tugas / Proyek</b>:\n<i>(Contoh: Skripsi Bab 4 dan 5 / Pembuatan Website Portofolio)</i>"
            );
            return;
        }

        // Step 2: Task Title -> Proceed to Deadline Step
        if ($step === 'awaiting_title') {
            $session['title'] = $input;
            $session['step'] = 'awaiting_deadline';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));

            $buttons = [
                [
                    ['text' => '⚡ Hari Ini (23:59 WIB)', 'callback_data' => 'dl_today'],
                    ['text' => '⏳ Besok (23:59 WIB)', 'callback_data' => 'dl_tomorrow'],
                ],
                [
                    ['text' => '🗓️ 3 Hari Lagi', 'callback_data' => 'dl_3days'],
                    ['text' => '🗓️ 7 Hari (1 Minggu)', 'callback_data' => 'dl_7days'],
                ],
                [
                    ['text' => '✏️ Ketik Tanggal Manual', 'callback_data' => 'dl_custom'],
                ],
                [
                    ['text' => '❌ Batalkan', 'callback_data' => 'wizard_cancel'],
                ]
            ];

            $this->telegram->sendMessage(
                $chatId,
                "✅ Proyek: <b>{$input}</b>\n\n"
                . "📅 <b>Langkah 3/5: Deadline / Jatuh Tempo</b>\nKapan tugas ini harus selesai?\n\nPilih preset di bawah atau ketik manual:",
                ['reply_markup' => json_encode(['inline_keyboard' => $buttons])]
            );
            return;
        }

        // Step 3b: Custom Deadline Text
        if ($step === 'awaiting_custom_deadline') {
            try {
                $parsedDate = Carbon::parse($input);
            } catch (\Exception $e) {
                $parsedDate = Carbon::now()->addDays(3)->endOfDay();
            }

            $session['deadline'] = $parsedDate->toDateTimeString();
            $session['step'] = 'awaiting_total_amount';
            Cache::put("tg_wizard_{$chatId}", $session, now()->addMinutes(30));

            $formattedDl = $parsedDate->translatedFormat('d F Y, H:i') . ' WIB';
            $this->telegram->sendMessage(
                $chatId,
                "✅ Deadline diset: <b>{$formattedDl}</b>\n\n"
                . "💰 <b>Langkah 4/5:</b>\nBerapa <b>Total Biaya Proyek</b>?\n<i>(Ketik angka saja, contoh: <code>250000</code> atau <code>1500000</code>)</i>"
            );
            return;
        }

        // Step 4: Total Amount
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
                . "💳 <b>Langkah 5/5:</b>\nPilih <b>Metode Pembayaran</b>:",
                ['reply_markup' => json_encode(['inline_keyboard' => $buttons])]
            );
            return;
        }

        // Step 5b: Custom DP
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
     * Show preview confirmation prompt before final rendering with format choices.
     */
    protected function showConfirmationPrompt(string $chatId, array $session): void
    {
        $category = Category::find($session['category_id'] ?? 1);
        $total = (float)($session['total_amount'] ?? 0);
        $isDp = ($session['payment_type'] ?? '') === 'dp';
        $dp = (float)($session['dp_amount'] ?? 0);
        $sisa = max(0, $total - $dp);

        $deadlineStr = isset($session['deadline']) 
            ? Carbon::parse($session['deadline'])->translatedFormat('d F Y, H:i') . ' WIB'
            : Carbon::now()->addDays(3)->translatedFormat('d F Y') . ', 23:59 WIB';

        $previewNumber = Invoice::generateInvoiceNumber($category?->id);

        $text = "📋 <b>RINGKASAN INVOICE BARU</b>\n"
              . "────────────────────────\n"
              . "• <b>No. Invoice:</b> <code>{$previewNumber}</code>\n"
              . "• <b>Kategori:</b> {$category->name}\n"
              . "• <b>Nama Klien:</b> {$session['client_name']}\n"
              . "• <b>Judul Tugas:</b> {$session['title']}\n"
              . "• <b>Deadline:</b> {$deadlineStr}\n"
              . "• <b>Total Biaya:</b> Rp " . number_format($total, 0, ',', '.') . "\n"
              . ($isDp 
                  ? "• <b>Metode:</b> Bertahap (DP)\n  - Wajib DP: <b>Rp " . number_format($dp, 0, ',', '.') . "</b>\n  - Sisa Pelunasan: Rp " . number_format($sisa, 0, ',', '.') . "\n"
                  : "• <b>Metode:</b> Bayar Lunas Langsung\n")
              . "────────────────────────\n"
              . "Pilih format file yang ingin dikirimkan ke chat Telegram ini:";

        $buttons = [
            [
                ['text' => '🖼️ Kirim Gambar (PNG)', 'callback_data' => 'confirm_png'],
                ['text' => '📄 Kirim Dokumen (PDF)', 'callback_data' => 'confirm_pdf'],
            ],
            [
                ['text' => '🚀 Kirim Keduanya (PNG + PDF)', 'callback_data' => 'confirm_both'],
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
     * Render high-resolution PNG invoice via Puppeteer.
     */
    public function renderPng(Invoice $invoice): ?string
    {
        $exportDir = storage_path('app/public/invoices/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $pngFilename = $invoice->invoice_number . '.png';
        $pngPath = $exportDir . '/' . $pngFilename;

        if (file_exists($pngPath) && filesize($pngPath) > 0) {
            return $pngPath;
        }

        $htmlContent = view('invoices.standalone', compact('invoice'))->render();
        $tempHtmlPath = storage_path('app/public/invoices/exports/tg_temp_png_' . $invoice->id . '_' . time() . '.html');
        file_put_contents($tempHtmlPath, $htmlContent);

        $scriptPath = base_path('render_image.mjs');
        $command = "node \"{$scriptPath}\" \"{$tempHtmlPath}\" \"{$pngPath}\" 2>&1";
        exec($command, $output, $returnCode);
        @unlink($tempHtmlPath);

        return (file_exists($pngPath) && filesize($pngPath) > 0) ? $pngPath : null;
    }

    /**
     * Render official 2-page A4 PDF invoice via Puppeteer with DomPDF fallback.
     */
    public function renderPdf(Invoice $invoice): ?string
    {
        $exportDir = storage_path('app/public/invoices/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $pdfFilename = $invoice->invoice_number . '.pdf';
        $pdfPath = $exportDir . '/' . $pdfFilename;

        if (file_exists($pdfPath) && filesize($pdfPath) > 0) {
            return $pdfPath;
        }

        $pdfHtmlContent = view('invoices.export', compact('invoice'))->render();
        $tempPdfHtmlPath = storage_path('app/public/invoices/exports/tg_temp_pdf_' . $invoice->id . '_' . time() . '.html');
        file_put_contents($tempPdfHtmlPath, $pdfHtmlContent);

        $scriptPath = base_path('render_pdf.mjs');
        $command = "node \"{$scriptPath}\" \"{$tempPdfHtmlPath}\" \"{$pdfPath}\" 2>&1";
        exec($command, $output, $returnCode);
        @unlink($tempPdfHtmlPath);

        if (file_exists($pdfPath) && filesize($pdfPath) > 0) {
            return $pdfPath;
        }

        // Fallback to DomPDF if Puppeteer timed out
        try {
            $pdf = Pdf::loadView('invoices.export', compact('invoice'))->setPaper('a4', 'portrait');
            $pdf->save($pdfPath);
            return file_exists($pdfPath) ? $pdfPath : null;
        } catch (\Exception $e) {
            Log::error('DomPDF fallback failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Execute invoice creation in database and send chosen format file(s).
     */
    protected function executeInvoiceCreation(string $chatId, array $session, string $format = 'both'): void
    {
        $this->telegram->sendMessage($chatId, "⏳ <i>Sedang membuat invoice dan merender file resmi...</i>");

        try {
            $category = Category::find($session['category_id'] ?? 1) ?: Category::first();
            $invoiceNumber = Invoice::generateInvoiceNumber($category->id);
            $isDp = ($session['payment_type'] ?? 'full') === 'dp';
            $dp = (float)($session['dp_amount'] ?? 0);
            $total = (float)$session['total_amount'];

            $deadline = isset($session['deadline']) 
                ? Carbon::parse($session['deadline']) 
                : Carbon::now()->addDays(3)->endOfDay();

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'title' => $session['title'],
                'client_name' => $session['client_name'],
                'category_id' => $category->id,
                'description' => "Pengerjaan {$session['title']} untuk {$session['client_name']}. Sesuai kesepakatan.",
                'deadline' => $deadline,
                'payment_type' => $isDp ? 'dp' : 'full',
                'dp_amount' => $isDp ? $dp : null,
                'total_amount' => $total,
                'status' => 'unpaid',
            ]);

            $clientUrl = $invoice->getClientViewUrl();
            $formattedTotal = 'Rp ' . number_format($total, 0, ',', '.');
            $formattedDp = $dp > 0 ? 'Rp ' . number_format($dp, 0, ',', '.') : '-';
            $sisa = $isDp ? 'Rp ' . number_format(max(0, $total - $dp), 0, ',', '.') : 'Rp 0';
            $formattedDl = $deadline->translatedFormat('d F Y, H:i') . ' WIB';

            // WhatsApp Share Text
            $brand = $category->brand_name ?: 'ABT-FREELANCE';
            $waMessage = "Halo {$session['client_name']}, berikut Invoice resmi dari *{$brand}*:\n\n"
                       . "📄 *Nomor:* {$invoice->invoice_number}\n"
                       . "📋 *Proyek:* {$session['title']}\n"
                       . "📅 *Deadline:* {$formattedDl}\n"
                       . "💰 *Total Biaya:* {$formattedTotal}\n"
                       . ($isDp ? "💵 *Tagihan DP:* {$formattedDp}\n" : "")
                       . "🔗 *Lihat Invoice & QRIS:* {$clientUrl}\n\n"
                       . "Mohon konfirmasi setelah transfer pembayaran ya. Terima kasih 🙏";

            $caption = "✅ <b>INVOICE RESMI BERHASIL DIBUAT!</b>\n\n"
                     . "📄 <b>Nomor:</b> <code>{$invoice->invoice_number}</code>\n"
                     . "👤 <b>Klien:</b> {$session['client_name']}\n"
                     . "📋 <b>Proyek:</b> {$session['title']}\n"
                     . "📅 <b>Deadline:</b> {$formattedDl}\n"
                     . "🏷️ <b>Kategori:</b> {$category->name}\n"
                     . "💰 <b>Total Biaya:</b> {$formattedTotal}\n"
                     . ($isDp ? "💳 <b>Wajib DP:</b> {$formattedDp} (Sisa: {$sisa})\n" : "💳 <b>Metode:</b> Bayar Lunas Langsung\n")
                     . "🌐 <b>Link Portal Klien:</b>\n{$clientUrl}\n\n"
                     . "📲 <b>Format Chat WhatsApp (Tinggal Salin):</b>\n"
                     . "<code>" . htmlspecialchars($waMessage) . "</code>";

            // Interactive callback buttons (100% safe from Telegram localhost url validation error)
            $keyboardButtons = [
                [
                    ['text' => '📄 Minta File PDF', 'callback_data' => "send_pdf_{$invoice->id}"],
                    ['text' => '🖼️ Minta File PNG', 'callback_data' => "send_png_{$invoice->id}"],
                ]
            ];

            // Only add external URL buttons if it's a valid public domain
            $isLocal = str_contains($clientUrl, 'localhost') || str_contains($clientUrl, '127.0.0.1');
            if (!$isLocal) {
                $keyboardButtons[] = [
                    ['text' => '🌐 Buka Portal Klien', 'url' => $clientUrl],
                    ['text' => '💬 Buka di Web Admin', 'url' => config('app.url') . "/invoices/{$invoice->id}"],
                ];
            }

            $replyMarkup = ['inline_keyboard' => $keyboardButtons];

            // 1. Send PNG if requested
            $pngSent = false;
            if ($format === 'png' || $format === 'both') {
                $pngPath = $this->renderPng($invoice);
                if ($pngPath && file_exists($pngPath)) {
                    $this->telegram->sendPhotoToChat($chatId, $pngPath, $caption, [
                        'reply_markup' => json_encode($replyMarkup)
                    ]);
                    $pngSent = true;
                }
            }

            // 2. Send PDF if requested
            if ($format === 'pdf' || $format === 'both') {
                $pdfPath = $this->renderPdf($invoice);
                if ($pdfPath && file_exists($pdfPath)) {
                    $docCaption = $pngSent ? "📄 Dokumen Resmi PDF: <b>{$invoice->invoice_number}</b>" : $caption;
                    $this->telegram->sendDocumentToChat($chatId, $pdfPath, $docCaption, [
                        'reply_markup' => json_encode($replyMarkup)
                    ]);
                }
            }

            // Fallback text if rendering failed
            if (!$pngSent && $format !== 'pdf') {
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
     * Legacy Fallback for /status command.
     */
    protected function handleInvoiceStatus(string $chatId, string $rawText): void
    {
        $query = trim(substr($rawText, 7));
        if (empty($query)) {
            $this->showStatusMenu($chatId);
            return;
        }

        if (is_numeric($query)) {
            $this->findAndShowInvoiceByNumber($chatId, (int)$query);
            return;
        }

        $invoice = Invoice::where('invoice_number', 'like', "%{$query}%")
            ->orWhere('client_name', 'like', "%{$query}%")
            ->latest('id')
            ->first();

        if (!$invoice) {
            $this->telegram->sendMessage($chatId, "❌ Invoice tidak ditemukan.");
            return;
        }

        $this->showInvoiceDetailCard($chatId, $invoice);
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
            'deadline' => Carbon::now()->addDays(3)->endOfDay()->toDateTimeString(),
            'category_id' => 1,
        ];

        if ($session['total_amount'] <= 0) {
            $this->telegram->sendMessage($chatId, "❌ Total biaya tidak valid. Silakan gunakan tombol <b>📄 Buat Invoice Baru</b>.", [
                'reply_markup' => json_encode($this->getMainKeyboard())
            ]);
            return;
        }

        $this->executeInvoiceCreation($chatId, $session, 'both');
    }
}
