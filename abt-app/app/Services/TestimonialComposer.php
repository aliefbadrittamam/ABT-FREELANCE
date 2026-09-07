<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TestimonialComposer
{
    /**
     * Compose 1 to 4 images into a clean, aesthetic canvas.
     * Images are NOT cropped — they are scaled proportionally (contain)
     * so every part of screenshots (chats, transfer proofs) stays fully visible.
     *
     * @param array<string> $imagePaths List of valid existing file paths
     * @param string $outputPath Destination file path
     * @return string
     */
    public function composeDynamic(array $imagePaths, string $outputPath): string
    {
        // Filter out empty paths or non-existent files
        $validPaths = array_values(array_filter($imagePaths, fn($p) => !empty($p) && file_exists($p)));
        $count = count($validPaths);

        if ($count === 0) {
            throw new \InvalidArgumentException("Minimal harus ada 1 gambar yang valid untuk dikomposisikan.");
        }

        $manager = new ImageManager(new Driver());
        $padding = 12;
        $canvasWidth = 1080;
        $canvasHeight = 1080;

        // 1 Image: Preserve full aspect ratio on white canvas — nothing is cropped.
        // Large/long screenshots are scaled down proportionally to fit 1080px max dimension.
        if ($count === 1) {
            $img = $manager->read($validPaths[0]);

            // Scale proportionally so the longest side is max 1056 (1080 - padding*2)
            // scaleDown() keeps aspect ratio and never enlarges small images
            $img->scaleDown(1056, 1056);

            $w = $img->width();
            $h = $img->height();

            $canvas = $manager->create($w + ($padding * 2), $h + ($padding * 2))->fill('ffffff');
            $canvas->place($img, 'top-left', $padding, $padding);
            $canvas->toJpeg(92)->save($outputPath);

            return $outputPath;
        }

        // 2 Images: Side-by-side 2 columns — each image fully visible (contained, no crop)
        if ($count === 2) {
            $canvas = $manager->create($canvasWidth, $canvasHeight)->fill('ffffff');
            $colWidth = (int)(($canvasWidth - ($padding * 3)) / 2);
            $colHeight = $canvasHeight - ($padding * 2);

            $img1 = $manager->read($validPaths[0])->contain($colWidth, $colHeight, 'ffffff', 'center');
            $img2 = $manager->read($validPaths[1])->contain($colWidth, $colHeight, 'ffffff', 'center');

            $canvas->place($img1, 'top-left', $padding, $padding);
            $canvas->place($img2, 'top-left', $colWidth + ($padding * 2), $padding);
            $canvas->toJpeg(92)->save($outputPath);

            return $outputPath;
        }

        // 3 Images: 1 Large Top + 2 Split Bottom — each image fully visible (contained, no crop)
        if ($count === 3) {
            $canvas = $manager->create($canvasWidth, $canvasHeight)->fill('ffffff');
            $topHeight = (int)(($canvasHeight - ($padding * 3)) * 0.52);
            $topWidth = $canvasWidth - ($padding * 2);
            $bottomHeight = $canvasHeight - ($padding * 3) - $topHeight;
            $bottomWidth = (int)(($canvasWidth - ($padding * 3)) / 2);

            $img1 = $manager->read($validPaths[0])->contain($topWidth, $topHeight, 'ffffff', 'center');
            $img2 = $manager->read($validPaths[1])->contain($bottomWidth, $bottomHeight, 'ffffff', 'center');
            $img3 = $manager->read($validPaths[2])->contain($bottomWidth, $bottomHeight, 'ffffff', 'center');

            $canvas->place($img1, 'top-left', $padding, $padding);
            $canvas->place($img2, 'top-left', $padding, $topHeight + ($padding * 2));
            $canvas->place($img3, 'top-left', $bottomWidth + ($padding * 2), $topHeight + ($padding * 2));
            $canvas->toJpeg(92)->save($outputPath);

            return $outputPath;
        }

        // 4 Images: Classic 2x2 Grid — each quadrant fully visible (contained, no crop)
        $canvas = $manager->create($canvasWidth, $canvasHeight)->fill('ffffff');
        $boxSize = (int)(($canvasWidth - ($padding * 3)) / 2);

        $positions = [
            [$padding, $padding],
            [$boxSize + ($padding * 2), $padding],
            [$padding, $boxSize + ($padding * 2)],
            [$boxSize + ($padding * 2), $boxSize + ($padding * 2)],
        ];

        foreach (array_slice($validPaths, 0, 4) as $i => $path) {
            $img = $manager->read($path)->contain($boxSize, $boxSize, 'ffffff', 'center');
            $canvas->place($img, 'top-left', $positions[$i][0], $positions[$i][1]);
        }

        $canvas->toJpeg(92)->save($outputPath);

        return $outputPath;
    }

    /**
     * Backward compatibility wrapper
     */
    public function compose(?string $tugasPath, ?string $chatPath, ?string $hasilPath, ?string $pelunasanPath, string $outputPath): string
    {
        $images = array_values(array_filter([$tugasPath, $chatPath, $hasilPath, $pelunasanPath]));
        return $this->composeDynamic($images, $outputPath);
    }
}
