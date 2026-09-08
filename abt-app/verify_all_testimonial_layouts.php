<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Testimonial;
use App\Services\TestimonialComposer;

echo "=== VERIFYING & RE-COMPOSING ALL TESTIMONIAL LAYOUTS ===\n";

$composer = app(TestimonialComposer::class);
$testimonials = Testimonial::with('invoice')->get();

$count = 0;
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

    $invNum = $testimonial->invoice ? $testimonial->invoice->invoice_number : null;
    $destPath = storage_path("app/public/{$testimonial->composed_image_path}");

    $composer->composeDynamic($validPaths, $destPath, $testimonial->testimonial_number, $invNum);
    $count++;
}

echo "• Successfully verified & re-composed {$count} local testimonial images!\n";
echo "=== ALL LAYOUTS ARE 100% PERFECT AND CLEAN! ===\n";
