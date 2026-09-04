<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Services\TestimonialComposer;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');
        $search = trim($request->query('search', ''));

        $query = $status === 'trash' ? Testimonial::onlyTrashed() : Testimonial::query();

        if ($search !== '') {
            $cleanSearch = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanSearch) {
                if (is_numeric($cleanSearch)) {
                    $q->orWhere('testimonial_number', (int)$cleanSearch);
                }
                $q->orWhere('major', 'like', "%{$search}%")
                  ->orWhere('task_title', 'like', "%{$search}%")
                  ->orWhere('deliverables', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        // Default sorting: newest testimonial number & created date first
        $testimonials = $query->orderBy('testimonial_number', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(12)
                              ->withQueryString();

        $activeCount = Testimonial::count();
        $trashCount = Testimonial::onlyTrashed()->count();

        return view('testimonials.index', compact('testimonials', 'status', 'search', 'activeCount', 'trashCount'));
    }

    public function create(Request $request)
    {
        $nextNumber = Testimonial::getNextTestimonialNumber();
        $fromInvoice = null;

        if ($request->filled('from_invoice')) {
            $fromInvoice = \App\Models\Invoice::with('category')->find($request->from_invoice);
        }

        return view('testimonials.create', compact('nextNumber', 'fromInvoice'));
    }

    public function store(Request $request, TestimonialComposer $composer, TelegramService $telegram)
    {
        $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'testimonial_number' => 'nullable|integer|min:1',
            'major' => 'nullable|string|max:255',
            'task_title' => 'nullable|string|max:255',
            'deliverables' => 'nullable|string|max:255',
            'image_tugas' => 'nullable|image|max:5120',
            'image_chat' => 'nullable|image|max:5120',
            'image_hasil' => 'nullable|image|max:5120',
            'image_pelunasan' => 'nullable|image|max:5120',
            'caption' => 'nullable|string|max:500',
            'client_name' => 'nullable|string|max:255',
        ]);

        // Validate at least 1 image is uploaded
        $hasAnyImage = $request->hasFile('image_tugas') ||
                       $request->hasFile('image_chat') ||
                       $request->hasFile('image_hasil') ||
                       $request->hasFile('image_pelunasan');

        if (!$hasAnyImage) {
            return back()->withInput()->with('error', 'Gagal membuat testimoni: Minimal harus mengunggah setidaknya 1 gambar bukti tugas.');
        }

        try {
            $paths = [
                'tugas' => null,
                'chat' => null,
                'hasil' => null,
                'pelunasan' => null,
            ];
            $uploadedAbsolutePaths = [];

            foreach (['tugas', 'chat', 'hasil', 'pelunasan'] as $slot) {
                if ($request->hasFile("image_{$slot}")) {
                    $paths[$slot] = $request->file("image_{$slot}")->store('testimonials/raw', 'public');
                    $uploadedAbsolutePaths[] = storage_path("app/public/{$paths[$slot]}");
                }
            }

            $composedDir = storage_path('app/public/testimonials/composed');
            if (!is_dir($composedDir)) {
                mkdir($composedDir, 0755, true);
            }
            $composedFilename = 'composed_' . time() . '_' . uniqid() . '.jpg';
            $composedPath = "testimonials/composed/{$composedFilename}";

            // Process image dynamically (1 to 4 images)
            $composer->composeDynamic($uploadedAbsolutePaths, storage_path("app/public/{$composedPath}"));

            $testiNumber = $request->filled('testimonial_number') 
                ? (int)$request->testimonial_number 
                : Testimonial::getNextTestimonialNumber();

            $testimonial = Testimonial::create([
                'invoice_id' => $request->invoice_id,
                'testimonial_number' => $testiNumber,
                'major' => $request->major,
                'task_title' => $request->task_title,
                'deliverables' => $request->deliverables,
                'image_tugas_path' => $paths['tugas'],
                'image_chat_path' => $paths['chat'],
                'image_hasil_path' => $paths['hasil'],
                'image_pelunasan_path' => $paths['pelunasan'],
                'composed_image_path' => $composedPath,
                'caption' => $request->caption,
                'client_name' => $request->client_name,
            ]);

            $telegramCaption = $testimonial->getFormattedTelegramCaption();
            $messageId = $telegram->sendPhoto(storage_path("app/public/{$composedPath}"), $telegramCaption);

            if ($messageId) {
                $testimonial->update([
                    'posted_to_telegram' => true,
                    'telegram_message_id' => $messageId,
                ]);
                return redirect()->route('testimonials.index')
                    ->with('success', "Testimoni #{$testiNumber} berhasil dibuat dan diposting ke Channel Telegram!");
            }

            $teleError = $telegram->getLastError();
            return redirect()->route('testimonials.index')
                ->with('warning', "Testimoni #{$testiNumber} berhasil disimpan di sistem lokal, tetapi gagal diposting ke Telegram. Detail: " . ($teleError ?: 'Koneksi terputus.'));

        } catch (\Exception $e) {
            Log::error('Testimonial store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memproses testimoni: ' . $e->getMessage());
        }
    }

    public function edit(Testimonial $testimonial)
    {
        return view('testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial, TestimonialComposer $composer, TelegramService $telegram)
    {
        $request->validate([
            'testimonial_number' => 'nullable|integer|min:1',
            'major' => 'nullable|string|max:255',
            'task_title' => 'nullable|string|max:255',
            'deliverables' => 'nullable|string|max:255',
            'image_tugas' => 'nullable|image|max:5120',
            'image_chat' => 'nullable|image|max:5120',
            'image_hasil' => 'nullable|image|max:5120',
            'image_pelunasan' => 'nullable|image|max:5120',
            'caption' => 'nullable|string|max:500',
            'client_name' => 'nullable|string|max:255',
        ]);

        try {
            $paths = [
                'tugas' => $testimonial->image_tugas_path,
                'chat' => $testimonial->image_chat_path,
                'hasil' => $testimonial->image_hasil_path,
                'pelunasan' => $testimonial->image_pelunasan_path,
            ];

            $hasChangedImage = false;
            foreach (['tugas', 'chat', 'hasil', 'pelunasan'] as $slot) {
                if ($request->hasFile("image_{$slot}")) {
                    $paths[$slot] = $request->file("image_{$slot}")->store('testimonials/raw', 'public');
                    $hasChangedImage = true;
                }
            }

            $composedPath = $testimonial->composed_image_path;

            // Collect existing and newly uploaded images
            $activeImageAbsolutePaths = [];
            foreach (['tugas', 'chat', 'hasil', 'pelunasan'] as $slot) {
                if (!empty($paths[$slot]) && file_exists(storage_path("app/public/{$paths[$slot]}"))) {
                    $activeImageAbsolutePaths[] = storage_path("app/public/{$paths[$slot]}");
                }
            }

            // Re-compose if images changed or if composed image was missing
            if ($hasChangedImage && !empty($activeImageAbsolutePaths)) {
                $composedFilename = 'composed_' . time() . '_' . uniqid() . '.jpg';
                $composedPath = "testimonials/composed/{$composedFilename}";
                $composer->composeDynamic($activeImageAbsolutePaths, storage_path("app/public/{$composedPath}"));
            }

            $testiNumber = $request->filled('testimonial_number') 
                ? (int)$request->testimonial_number 
                : ($testimonial->testimonial_number ?: Testimonial::getNextTestimonialNumber());

            $testimonial->update([
                'testimonial_number' => $testiNumber,
                'major' => $request->major,
                'task_title' => $request->task_title,
                'deliverables' => $request->deliverables,
                'image_tugas_path' => $paths['tugas'],
                'image_chat_path' => $paths['chat'],
                'image_hasil_path' => $paths['hasil'],
                'image_pelunasan_path' => $paths['pelunasan'],
                'composed_image_path' => $composedPath,
                'caption' => $request->caption,
                'client_name' => $request->client_name,
            ]);

            // Sync update with Telegram post if exists
            if ($testimonial->posted_to_telegram && $testimonial->telegram_message_id && $composedPath) {
                $telegramCaption = $testimonial->getFormattedTelegramCaption();

                $updated = $telegram->editMessageMedia(
                    $testimonial->telegram_message_id,
                    storage_path("app/public/{$composedPath}"),
                    $telegramCaption
                );

                if (!$updated) {
                    $teleError = $telegram->getLastError();
                    return redirect()->route('testimonials.index')
                        ->with('warning', "Testimoni #{$testiNumber} berhasil diperbarui di lokal, tapi gagal sinkron ke Telegram: " . ($teleError ?: 'Cek izin bot.'));
                }
            }

            return redirect()->route('testimonials.index')->with('success', "Testimoni #{$testiNumber} berhasil diperbarui!");

        } catch (\Exception $e) {
            Log::error('Testimonial update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal memperbarui testimoni: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Testimonial $testimonial, TelegramService $telegram)
    {
        $testiNumber = $testimonial->testimonial_number;

        // 7-day Deletion Protection Check
        if (!$testimonial->isDeletable()) {
            return back()->with('error', "⛔ Testimoni #{$testiNumber} sudah berusia lebih dari 7 hari (dibuat pada {$testimonial->created_at->translatedFormat('d F Y')}) dan telah dikunci permanen untuk melindungi arsip portofolio.");
        }

        $deleteFromTelegram = $request->boolean('delete_from_telegram');

        // Only delete from Telegram if explicitly requested by user
        if ($deleteFromTelegram && $testimonial->posted_to_telegram && $testimonial->telegram_message_id) {
            $teleDeleted = $telegram->deleteMessage($testimonial->telegram_message_id);
            if (!$teleDeleted) {
                $teleError = $telegram->getLastError();
                Log::warning("Could not delete telegram message #{$testimonial->telegram_message_id}: {$teleError}");
            }
            $testimonial->update([
                'posted_to_telegram' => false,
                'telegram_message_id' => null,
            ]);
        }

        // Soft delete the testimonial record (files remain safe in storage)
        $testimonial->delete();

        $msg = $deleteFromTelegram 
            ? "Testimoni #{$testiNumber} berhasil dipindahkan ke Sampah & dihapus dari Telegram!"
            : "Testimoni #{$testiNumber} berhasil dipindahkan ke Sampah. (Postingan di Telegram tetap aman terjaga).";

        return redirect()->route('testimonials.index')->with('success', $msg);
    }

    public function restore(int $id)
    {
        $testimonial = Testimonial::onlyTrashed()->findOrFail($id);
        $testiNumber = $testimonial->testimonial_number;
        $testimonial->restore();

        return redirect()->route('testimonials.index', ['status' => 'trash'])->with('success', "Testimoni #{$testiNumber} berhasil dipulihkan!");
    }

    public function forceDelete(Request $request, int $id, TelegramService $telegram)
    {
        $testimonial = Testimonial::onlyTrashed()->findOrFail($id);
        $testiNumber = $testimonial->testimonial_number;

        // 7-day Deletion Protection Check
        if (!$testimonial->isDeletable()) {
            return back()->with('error', "⛔ Testimoni #{$testiNumber} sudah berusia lebih dari 7 hari dan telah terkunci permanen.");
        }

        $deleteFromTelegram = $request->boolean('delete_from_telegram');

        if ($deleteFromTelegram && $testimonial->posted_to_telegram && $testimonial->telegram_message_id) {
            $telegram->deleteMessage($testimonial->telegram_message_id);
        }

        // Permanently clean files
        foreach (['image_tugas_path', 'image_chat_path', 'image_hasil_path', 'image_pelunasan_path', 'composed_image_path'] as $field) {
            if ($testimonial->$field && Storage::disk('public')->exists($testimonial->$field)) {
                Storage::disk('public')->delete($testimonial->$field);
            }
        }

        $testimonial->forceDelete();

        return redirect()->route('testimonials.index', ['status' => 'trash'])->with('success', "Testimoni #{$testiNumber} berhasil dihapus permanen!");
    }
}
