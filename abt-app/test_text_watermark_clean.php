<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());

$width = 1080;
$height = 1080;

$canvas = $manager->create($width, $height)->fill('0c0d10');
$core = $canvas->core()->native();

$gridColor = imagecolorallocate($core, 22, 25, 32);      // Tech grid lines
$textColor = imagecolorallocate($core, 38, 44, 28);      // Subtle ABTJOKI watermark text pattern

// 1. Grid Lines
for ($x = 0; $x <= $width; $x += 60) {
    imageline($core, $x, 0, $x, $height, $gridColor);
}
for ($y = 0; $y <= $height; $y += 60) {
    imageline($core, 0, $y, $width, $y, $gridColor);
}

// 2. Staggered ABTJOKI Watermark Text Pattern
$text = "ABTJOKI";
for ($y = 15; $y < $height; $y += 65) {
    $shift = (($y / 65) % 2 === 0) ? 10 : 70;
    for ($x = $shift; $x < $width; $x += 140) {
        imagestring($core, 4, $x, $y, $text, $textColor);
    }
}

// 3. Create Center Text Watermark "ABT JOKI" (No white background, pure transparent text!)
// Draw a clean text badge or text overlay
$wmTextCanvas = $manager->create(600, 150)->fill('000000');
$wmCore = $wmTextCanvas->core()->native();

// Set black color transparent
imagecolortransparent($wmCore, imagecolorallocate($wmCore, 0, 0, 0));

$wmColor = imagecolorallocate($wmCore, 232, 255, 0); // Neon Yellow color for center text

// Scale text by drawing onto small canvas then resizing
$subCanvas = $manager->create(180, 45)->fill('000000');
$subCore = $subCanvas->core()->native();
imagecolortransparent($subCore, imagecolorallocate($subCore, 0, 0, 0));
imagestring($subCore, 5, 20, 14, "ABT JOKI", $wmColor);

// Scale up to 500px wide for big bold center text
$subCanvas->scale(500, 125);

// Place center watermark with 10% opacity (90% transparent - subtle glowing watermark!)
$canvas->place($subCanvas, 'center', 0, 0, 12);

$out = storage_path('app/public/test_clean_text_wm.jpg');
$canvas->toJpeg(92)->save($out);

echo "Clean text watermark generated at {$out}!\n";
