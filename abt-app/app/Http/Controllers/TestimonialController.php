<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Services\TestimonialComposer;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::latest()->paginate(12);
        return view('testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        $nextNumber = Testimonial::getNextTestimonialNumber();
        return view('testimonials.create', compact('nextNumber'));
    }

    public function store(Request $request, TestimonialComposer $composer, TelegramService $telegram)
    {
        $request->validate([
            'testimonial_number' => 'nullable|integer|min:1',
            'major' => 'nullable|string|max:255',
            'task_title' => 'nullable|string|max:255',
            'deliverables' => 'nullable|string|max:255',
            'image_tugas' => 'required|image|max:5120',
            'image_chat' => 'required|image|max:5120',
            'image_hasil' => 'required|image|max:5120',
            'image_pelunasan' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:500',
            'client_name' => 'nullable|string|max:255',
        ]);

        $paths = [];
        foreach (['tugas', 'chat', 'hasil', 'pelunasan'] as $slot) {
            $paths[$slot] = $request->file("image_{$slot}")->store('testimonials/raw', 'public');
        }

        $composedDir = storage_path('app/public/testimonials/composed');
        if (!is_dir($composedDir)) {
            mkdir($composedDir, 0755, true);
        }
        $composedFilename = 'composed_' . time() . '.jpg';
        $composedPath = "testimonials/composed/{$composedFilename}";

        $composer->compose(
            storage_path("app/public/{$paths['tugas']}"),
            storage_path("app/public/{$paths['chat']}"),
            storage_path("app/public/{$paths['hasil']}"),
            storage_path("app/public/{$paths['pelunasan']}"),
            storage_path("app/public/{$composedPath}")
        );

        $testiNumber = $request->filled('testimonial_number') 
            ? (int)$request->testimonial_number 
            : Testimonial::getNextTestimonialNumber();

        $testimonial = Testimonial::create([
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
        }

        $status = $messageId ? "Testimoni #{$testiNumber} berhasil dibuat & diposting ke Telegram!" : "Testimoni #{$testiNumber} berhasil dibuat! (Telegram belum dikonfigurasi/gagal kirim)";
        return redirect()->route('testimonials.index')->with('success', $status);
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

        $paths = [
            'tugas' => $testimonial->image_tugas_path,
            'chat' => $testimonial->image_chat_path,
            'hasil' => $testimonial->image_hasil_path,
            'pelunasan' => $testimonial->image_pelunasan_path,
        ];

        foreach (['tugas', 'chat', 'hasil', 'pelunasan'] as $slot) {
            if ($request->hasFile("image_{$slot}")) {
                $paths[$slot] = $request->file("image_{$slot}")->store('testimonials/raw', 'public');
            }
        }

        $composedFilename = 'composed_' . time() . '.jpg';
        $composedPath = "testimonials/composed/{$composedFilename}";

        $composer->compose(
            storage_path("app/public/{$paths['tugas']}"),
            storage_path("app/public/{$paths['chat']}"),
            storage_path("app/public/{$paths['hasil']}"),
            storage_path("app/public/{$paths['pelunasan']}"),
            storage_path("app/public/{$composedPath}")
        );

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

        if ($testimonial->posted_to_telegram && $testimonial->telegram_message_id) {
            $telegramCaption = $testimonial->getFormattedTelegramCaption();

            $updated = $telegram->editMessageMedia(
                $testimonial->telegram_message_id,
                storage_path("app/public/{$composedPath}"),
                $telegramCaption
            );

            if (!$updated) {
                return redirect()->route('testimonials.index')->with('warning', "Testimoni #{$testiNumber} berhasil diperbarui, tapi update Telegram gagal. Cek manual.");
            }
        }

        return redirect()->route('testimonials.index')->with('success', "Testimoni #{$testiNumber} berhasil diperbarui!");
    }
}
