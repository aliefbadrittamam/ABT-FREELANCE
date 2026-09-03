<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TestimonialComposer
{
    public function compose(string $tugasPath, string $chatPath, string $hasilPath, string $pelunasanPath, string $outputPath): string
    {
        $manager = new ImageManager(new Driver());
        $size = 540;
        $padding = 10;
        $canvasSize = ($size * 2) + ($padding * 3);

        $canvas = $manager->create($canvasSize, $canvasSize)->fill('ffffff');

        $images = [$tugasPath, $chatPath, $hasilPath, $pelunasanPath];
        $positions = [
            [$padding, $padding],
            [$size + ($padding * 2), $padding],
            [$padding, $size + ($padding * 2)],
            [$size + ($padding * 2), $size + ($padding * 2)],
        ];

        foreach ($images as $i => $imgPath) {
            $img = $manager->read($imgPath)->cover($size, $size);
            $canvas->place($img, 'top-left', $positions[$i][0], $positions[$i][1]);
        }

        $canvas->toJpeg(90)->save($outputPath);

        return $outputPath;
    }
}
