<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Testimonial;

$t35 = Testimonial::where('testimonial_number', 35)->first();

if ($t35) {
    echo "ID: {$t35->id}\n";
    echo "Number: {$t35->testimonial_number}\n";
    echo "Composed path: {$t35->composed_image_path}\n";
    echo "Telegram Message ID: {$t35->telegram_message_id}\n";
    echo "Tugas: {$t35->image_tugas_path}\n";
    echo "Chat: {$t35->image_chat_path}\n";
    echo "Hasil: {$t35->image_hasil_path}\n";
    echo "Pelunasan: {$t35->image_pelunasan_path}\n";

    $file = storage_path('app/public/' . $t35->composed_image_path);
    if (file_exists($file)) {
        $info = getimagesize($file);
        echo "File size: " . filesize($file) . " bytes\n";
        echo "Dimensions: {$info[0]} x {$info[1]}\n";
    } else {
        echo "File does not exist!\n";
    }
} else {
    echo "Testimonial #35 not found!\n";
}
