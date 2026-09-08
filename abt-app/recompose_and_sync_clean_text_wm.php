<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Testimonial;
use App\Services\TestimonialComposer;
use App\Services\TelegramService;

echo "=== RE-COMPOSING TESTIMONIALS WITH CLEAN TRANSPARENT 'ABT JOKI' TEXT WATERMARK ===\n";

$composer = app(TestimonialComposer::class);
$telegram = app(TelegramService::class);

$testimonials = Testimonial::all();
$recomposedCount = 0;

foreach ($testimonials as $testimonial) {
    $rawSlots = [
        'tugas' => $testimonial->image_tugas_path,
        'chat' => $testimonial->image_chat_path,
        'hasil' => $testimonial->image_hasil_path,
        'pelunasan' => $testimonial->image_pelunasan_path,
    ];

    $validPaths = [];
    foreach ($rawSlots as $slot => $path) {
        if (!empty($path)) {
            $abs = storage_path("app/public/{$path}");
            if (file_exists($abs)) {
                $validPaths[] = $abs;
            }
        }
    }

    if (count($validPaths) === 0 && $testimonial->composed_image_path && file_exists(storage_path("app/public/{$testimonial->composed_image_path}"))) {
        $validPaths[] = storage_path("app/public/{$testimonial->composed_image_path}");
    }

    if (count($validPaths) === 0) continue;

    $destPath = storage_path("app/public/{$testimonial->composed_image_path}");
    $composer->composeDynamic($validPaths, $destPath);
    $recomposedCount++;
}

echo "• Successfully re-composed {$recomposedCount} local testimonial images with Clean Transparent Text Watermark!\n";

// Sync to Telegram Channel
$postedTestimonials = Testimonial::where('posted_to_telegram', true)
    ->whereNotNull('telegram_message_id')
    ->orderBy('testimonial_number', 'asc')
    ->get();

echo "\nSyncing to Telegram Channel ({$postedTestimonials->count()} posts)...\n";

$syncedCount = 0;
foreach ($postedTestimonials as $testimonial) {
    $imgPath = storage_path('app/public/' . $testimonial->composed_image_path);
    if (!is_file($imgPath)) continue;

    $caption = $testimonial->getFormattedTelegramCaption();
    $updated = $telegram->editMessageMedia($testimonial->telegram_message_id, $imgPath, $caption);

    if ($updated) {
        $syncedCount++;
        echo "• #{$testimonial->testimonial_number}: ✅ SYNCED CLEAN TEXT WATERMARK\n";
    }
    usleep(250000); // 250ms delay
}

echo "\n=======================================================\n";
echo "SUCCESSFULLY SYNCED {$syncedCount} POSTINGS TO TELEGRAM CHANNEL!\n";
echo "=======================================================\n";
