<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class TelegramBotHandler
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
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

        // Handle commands
        if (str_starts_with($text, '/start') || str_starts_with($text, '/help') || str_starts_with($text, '/bantuan')) {
            $this->sendHelpMessage($chatId, $userName);
            return;
        }

        if (str_starts_with($text, '/inv')) {
            $this->handleInvoiceGeneration($chatId, $text);
            return;
        }

        if (str_starts_with($text, '/status')) {
            $this->handleInvoiceStatus($chatId, $text);
            return;
        }

        // Default response if not recognized
        $this->telegram->sendMessage($chatId, "Perintah tidak dikenali. Ketik /help untuk melihat panduan pembuatan invoice cepat via bot.");
    }

    /**
     * Send help and instruction message.
     */
    protected function sendHelpMessage(string $chatId, string $userName): void
    {
        $msg = "⚡ <b>Halo, {$userName}! Selamat datang di Bot Invoice ABT-FREELANCE</b>\n\n"
             . "Anda dapat membuat invoice resmi secara instan langsung dari Telegram ini tanpa perlu membuka browser laptop.\n\n"
             . "📌 <b>FORMAT PEMBUATAN INVOICE CEPAT:</b>\n"
             . "<code>/inv Judul Proyek | Nama Klien | Total Biaya | DP (Opsional) | Kategori (Opsional)</code>\n\n"
             . "💡 <b>Contoh 1 (Bayar Lunas):</b>\n"
             . "<code>/inv Makalah Sistem Informasi | Sarah Putri | 150000</code>\n\n"
             . "💡 <b>Contoh 2 (Dengan DP 50%):</b>\n"
             . "<code>/inv Jasa Pembuatan Website | Dimas Pratama | 1000000 | 500000</code>\n\n"
             . "💡 <b>Contoh 3 (Lengkap):</b>\n"
             . "<code>/inv Skripsi Bab 4-5 | Rizky | 350000 | 150000 | Joki</code>\n\n"
             . "✨ <i>Bot akan otomatis membuatkan invoice, merender gambar invoice HD, dan mengirimkannya ke chat Anda lengkap dengan link klien!</i>";

        $this->telegram->sendMessage($chatId, $msg);
    }

    /**
     * Handle the /inv command to parse, save, render, and return invoice.
     */
    protected function handleInvoiceGeneration(string $chatId, string $rawText): void
    {
        // Strip "/inv" prefix
        $content = trim(substr($rawText, 4));

        if (empty($content)) {
            $this->telegram->sendMessage(
                $chatId,
                "⚠️ <b>Format perintah kurang lengkap!</b>\n\n"
                . "Kirim dengan format pemisah garis lurus (<code>|</code>):\n"
                . "<code>/inv Judul Proyek | Nama Klien | Total Biaya | DP (Opsional)</code>\n\n"
                . "<b>Contoh siap pakai:</b>\n"
                . "<code>/inv Revisi Skripsi Bab 4 | Anita Rahma | 250000 | 100000</code>"
            );
            return;
        }

        $parts = array_map('trim', explode('|', $content));

        $title = $parts[0] ?? '';
        $clientName = $parts[1] ?? '';
        $totalRaw = $parts[2] ?? '';
        $dpRaw = $parts[3] ?? null;
        $categoryRaw = $parts[4] ?? null;

        if (empty($title) || empty($clientName) || empty($totalRaw)) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ <b>Data wajib belum lengkap!</b>\n"
                . "Pastikan minimal mengisi: <b>Judul | Nama Klien | Total Biaya</b>\n\n"
                . "Contoh: <code>/inv Joki Tugas Algoritma | Budi | 150000</code>"
            );
            return;
        }

        // Clean numbers
        $totalAmount = (float)preg_replace('/[^0-9]/', '', $totalRaw);
        $dpAmount = ($dpRaw !== null && $dpRaw !== '') ? (float)preg_replace('/[^0-9]/', '', $dpRaw) : 0;
        $paymentType = $dpAmount > 0 ? 'dp' : 'full';

        if ($totalAmount <= 0) {
            $this->telegram->sendMessage($chatId, "❌ Total biaya proyek tidak valid.");
            return;
        }

        // Determine category
        $category = null;
        if (!empty($categoryRaw)) {
            $category = Category::where('name', 'like', "%{$categoryRaw}%")
                ->orWhere('invoice_prefix', 'like', "%{$categoryRaw}%")
                ->first();
        }
        if (!$category) {
            // Default to Joki Tugas or first category
            $category = Category::first();
        }

        // Inform user that processing has started
        $this->telegram->sendMessage($chatId, "⏳ <i>Sedang memproses & merender Invoice resmi ABT-FREELANCE...</i>");

        try {
            $invoiceNumber = Invoice::generateInvoiceNumber($category->id);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'title' => $title,
                'client_name' => $clientName,
                'category_id' => $category->id,
                'description' => "Pengerjaan {$title} untuk {$clientName}. Sesuai instruksi dan kesepakatan.",
                'deadline' => now()->addDays(3),
                'payment_type' => $paymentType,
                'dp_amount' => $paymentType === 'dp' ? $dpAmount : null,
                'total_amount' => $totalAmount,
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
            $formattedTotal = 'Rp ' . number_format($totalAmount, 0, ',', '.');
            $formattedDp = $dpAmount > 0 ? 'Rp ' . number_format($dpAmount, 0, ',', '.') : '-';
            $sisa = $paymentType === 'dp' ? 'Rp ' . number_format(max(0, $totalAmount - $dpAmount), 0, ',', '.') : 'Rp 0';

            // WhatsApp Share Text
            $brand = $category->brand_name ?: 'ABT-FREELANCE';
            $tagihanSekarang = $paymentType === 'dp' ? $formattedDp : $formattedTotal;
            $waMessage = "Halo {$clientName}, berikut Invoice resmi dari *{$brand}*:\n\n"
                       . "📄 *Nomor:* {$invoice->invoice_number}\n"
                       . "📋 *Proyek:* {$title}\n"
                       . "💰 *Total Biaya:* {$formattedTotal}\n"
                       . ($paymentType === 'dp' ? "💵 *Tagihan DP:* {$formattedDp}\n" : "")
                       . "🔗 *Lihat Invoice & QRIS:* {$clientUrl}\n\n"
                       . "Mohon konfirmasi setelah pembayaran ya. Terima kasih 🙏";

            $caption = "✅ <b>INVOICE BERHASIL DIBUAT!</b>\n\n"
                     . "📄 <b>Nomor:</b> <code>{$invoice->invoice_number}</code>\n"
                     . "👤 <b>Klien:</b> {$clientName}\n"
                     . "📋 <b>Proyek:</b> {$title}\n"
                     . "🏷️ <b>Kategori:</b> {$category->name}\n"
                     . "💰 <b>Total Biaya:</b> {$formattedTotal}\n"
                     . ($paymentType === 'dp' ? "💳 <b>Wajib DP:</b> {$formattedDp} (Sisa: {$sisa})\n" : "💳 <b>Metode:</b> Bayar Lunas\n")
                     . "🌐 <b>Link Portal Klien:</b>\n{$clientUrl}\n\n"
                     . "📲 <b>Format Chat WhatsApp Klien (Tinggal Salin):</b>\n"
                     . "<code>" . htmlspecialchars($waMessage) . "</code>";

            // If image rendered successfully, send photo with caption
            if (file_exists($pngPath) && filesize($pngPath) > 0) {
                $this->telegram->sendPhotoToChat($chatId, $pngPath, $caption, [
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                ['text' => '🌐 Buka Portal Klien', 'url' => $clientUrl],
                                ['text' => '💬 Buka di Web Admin', 'url' => config('app.url') . "/invoices/{$invoice->id}"],
                            ]
                        ]
                    ])
                ]);
            } else {
                // Fallback text if rendering is slow
                $this->telegram->sendMessage($chatId, $caption);
            }

        } catch (\Exception $e) {
            Log::error('Telegram handleInvoiceGeneration failed', ['error' => $e->getMessage()]);
            $this->telegram->sendMessage($chatId, "❌ <b>Gagal membuat invoice:</b> " . $e->getMessage());
        }
    }

    /**
     * Check invoice status.
     */
    protected function handleInvoiceStatus(string $chatId, string $rawText): void
    {
        $query = trim(substr($rawText, 7));
        if (empty($query)) {
            $this->telegram->sendMessage($chatId, "Gunakan: <code>/status INV-JOKI-...</code> atau ketik ID/nomornya.");
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
}
