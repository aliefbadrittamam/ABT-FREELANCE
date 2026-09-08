<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());

function createBrandedBackground(ImageManager $manager, int $width, int $height) {
    $canvas = $manager->create($width, $height)->fill('0c0d10');
    $core = $canvas->core()->native();

    $gridColor = imagecolorallocate($core, 22, 25, 32);
    $textColor = imagecolorallocate($core, 38, 44, 28);

    for ($x = 0; $x <= $width; $x += 60) {
        imageline($core, $x, 0, $x, $height, $gridColor);
    }
    for ($y = 0; $y <= $height; $y += 60) {
        imageline($core, 0, $y, $width, $y, $gridColor);
    }

    $text = "ABTJOKI";
    for ($y = 15; $y < $height; $y += 65) {
        $shift = (($y / 65) % 2 === 0) ? 10 : 70;
        for ($x = $shift; $x < $width; $x += 140) {
            imagestring($core, 4, $x, $y, $text, $textColor);
        }
    }

    return $canvas;
}

function frameImage(ImageManager $manager, string $imagePath, int $boxWidth, int $boxHeight, string $bgColor = '13141a') {
    $borderSize = 3;
    $frame = $manager->create($boxWidth, $boxHeight)->fill('e8ff00');
    
    $innerW = max(1, $boxWidth - ($borderSize * 2));
    $innerH = max(1, $boxHeight - ($borderSize * 2));
    $innerBox = $manager->create($innerW, $innerH)->fill($bgColor);
    
    $img = $manager->read($imagePath)->contain($innerW, $innerH, $bgColor, 'center');
    $innerBox->place($img, 'center');
    
    $frame->place($innerBox, 'top-left', $borderSize, $borderSize);
    return $frame;
}

function applyTopCenterWatermark(ImageManager $manager, $canvas, int $width, int $height) {
    $subCanvas = $manager->create(220, 50)->fill('000000');
    $subCore = $subCanvas->core()->native();
    imagecolortransparent($subCore, imagecolorallocate($subCore, 0, 0, 0));
    
    $wmColor = imagecolorallocate($subCore, 232, 255, 0);
    imagestring($subCore, 5, 25, 16, "ABT JOKI", $wmColor);
    
    $targetW = (int)($width * 0.45);
    $targetH = (int)($targetW * 0.25);
    $subCanvas->scale($targetW, $targetH);
    
    $canvas->place($subCanvas, 'center', 0, 0, 12);
}

$sample1 = 'D:/ABT-FREELANCE/abt-app/public/assets/logo-abt-efootball-tur.jpg';
$sample2 = 'D:/ABT-FREELANCE/abt-app/logo.png';

$canvas = createBrandedBackground($manager, 1080, 1080);
$framed1 = frameImage($manager, $sample1, 516, 1040);
$framed2 = frameImage($manager, $sample2, 516, 1040);

$canvas->place($framed1, 'top-left', 16, 20);
$canvas->place($framed2, 'top-left', 548, 20);

applyTopCenterWatermark($manager, $canvas, 1080, 1080);

$outPath = storage_path('app/public/test_final_text_wm.jpg');
$canvas->toJpeg(92)->save($outPath);

echo "New clean test composite saved to {$outPath}!\n";
