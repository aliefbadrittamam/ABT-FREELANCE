<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Testimonial;
use App\Services\TelegramService;

echo "=== SYNCING PERFECT CLEAN TESTIMONIALS TO TELEGRAM CHANNEL ===\n";

$telegram = app(TelegramService::class);

$postedTestimonials = Testimonial::with('invoice')
    ->where('posted_to_telegram', true)
    ->whereNotNull('telegram_message_id')
    ->orderBy('testimonial_number', 'asc')
    ->get();

echo "Memproses " . $postedTestimonials->count() . " postingan di Telegram Channel...\n\n";

$synced = 0;
$failed = 0;

foreach ($postedTestimonials as $testimonial) {
    if (empty($testimonial->composed_image_path)) continue;

    $imgPath = storage_path('app/public/' . $testimonial->composed_image_path);
    if (!is_file($imgPath)) continue;

    $caption = $testimonial->getFormattedTelegramCaption();
    $updated = $telegram->editMessageMedia($testimonial->telegram_message_id, $imgPath, $caption);

    if ($updated) {
        $synced++;
        echo "• #{$testimonial->testimonial_number} (Msg ID {$testimonial->telegram_message_id}): ✅ SYNCED PERFECT LAYOUT\n";
    } else {
        $failed++;
        $err = $telegram->getLastError();
        echo "• #{$testimonial->testimonial_number}: ❌ FAILED - {$err}\n";
    }

    usleep(250000); // 250ms rate limit friendly
}

echo "\n=======================================================\n";
echo "TELEGRAM SYNC SUMMARY:\n";
echo "• Successfully Updated: {$synced} posts\n";
echo "• Failed / Skipped: {$failed} posts\n";
echo "=======================================================\n";
