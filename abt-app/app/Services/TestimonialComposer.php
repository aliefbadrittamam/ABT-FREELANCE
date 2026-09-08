<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TestimonialComposer
{
    /**
     * Compose 1 to 4 images into a clean, professional dark theme canvas.
     * Design Structure:
     * - Midnight Charcoal Dark Background (#0c0d10) + Tech Grid + Subtle ABTJOKI Watermark Pattern
     * - Top Header Bar:
     *   * Left: Testimonial Number (#XX)
     *   * Right: Invoice Number (INV-XXXX)
     * - Middle Area: Screenshots contained inside 3px Neon Yellow (#E8FF00) Framed Boxes
     * - NO logos or center overlays on top of screenshot content!
     *
     * @param array<string> $imagePaths List of valid existing file paths
     * @param string $outputPath Destination file path
     * @param string|int|null $testiNumber Testimonial Number (e.g. 14)
     * @param string|null $invoiceNumber Invoice Number (e.g. INV-JOKI-082-260904)
     * @return string
     */
    public function composeDynamic(array $imagePaths, string $outputPath, $testiNumber = null, ?string $invoiceNumber = null): string
    {
        $validPaths = array_values(array_filter($imagePaths, fn($p) => !empty($p) && file_exists($p)));
        $count = count($validPaths);

        if ($count === 0) {
            throw new \InvalidArgumentException("Minimal harus ada 1 gambar yang valid untuk dikomposisikan.");
        }

        $manager = new ImageManager(new Driver());
        $canvasWidth = 1080;
        $canvasHeight = 1080;

        // 1 Image Layout
        if ($count === 1) {
            $img = $manager->read($validPaths[0]);
            $img->scaleDown(1020, 990);

            $w = $img->width();
            $h = $img->height();

            $boxW = $w + 20;
            $boxH = $h + 20;

            $canvasW = max($canvasWidth, $boxW + 40);
            $canvasH = max($canvasHeight, $boxH + 60);

            $canvas = $this->createBrandedBackground($manager, $canvasW, $canvasH, $testiNumber, $invoiceNumber);
            $framed = $this->frameImage($manager, $validPaths[0], $boxW, $boxH);

            $canvas->place($framed, 'top-left', (int)(($canvasW - $boxW) / 2), (int)(($canvasH - $boxH + 40) / 2));
            $canvas->toJpeg(92)->save($outputPath);

            return $outputPath;
        }

        // 2 Images Layout: Side-by-Side
        if ($count === 2) {
            $canvas = $this->createBrandedBackground($manager, $canvasWidth, $canvasHeight, $testiNumber, $invoiceNumber);
            $boxWidth = 516;
            $boxHeight = 1000;

            $framed1 = $this->frameImage($manager, $validPaths[0], $boxWidth, $boxHeight);
            $framed2 = $this->frameImage($manager, $validPaths[1], $boxWidth, $boxHeight);

            $canvas->place($framed1, 'top-left', 16, 55);
            $canvas->place($framed2, 'top-left', 548, 55);

            $canvas->toJpeg(92)->save($outputPath);

            return $outputPath;
        }

        // 3 Images Layout: 1 Large Top + 2 Split Bottom
        if ($count === 3) {
            $canvas = $this->createBrandedBackground($manager, $canvasWidth, $canvasHeight, $testiNumber, $invoiceNumber);
            $topWidth = 1048;
            $topHeight = 495;
            $bottomWidth = 516;
            $bottomHeight = 490;

            $framed1 = $this->frameImage($manager, $validPaths[0], $topWidth, $topHeight);
            $framed2 = $this->frameImage($manager, $validPaths[1], $bottomWidth, $bottomHeight);
            $framed3 = $this->frameImage($manager, $validPaths[2], $bottomWidth, $bottomHeight);

            $canvas->place($framed1, 'top-left', 16, 55);
            $canvas->place($framed2, 'top-left', 16, 565);
            $canvas->place($framed3, 'top-left', 548, 565);

            $canvas->toJpeg(92)->save($outputPath);

            return $outputPath;
        }

        // 4 Images Layout: Classic 2x2 Grid
        $canvas = $this->createBrandedBackground($manager, $canvasWidth, $canvasHeight, $testiNumber, $invoiceNumber);
        $boxSize = 506;

        $positions = [
            [16, 55],
            [548, 55],
            [16, 565],
            [548, 565],
        ];

        foreach (array_slice($validPaths, 0, 4) as $i => $path) {
            $framed = $this->frameImage($manager, $path, $boxSize, $boxSize);
            $canvas->place($framed, 'top-left', $positions[$i][0], $positions[$i][1]);
        }

        $canvas->toJpeg(92)->save($outputPath);

        return $outputPath;
    }

    /**
     * Create branded Dark Background with Grid, ABTJOKI Watermark Pattern & Header Info
     */
    private function createBrandedBackground(ImageManager $manager, int $width, int $height, $testiNumber = null, ?string $invoiceNumber = null)
    {
        $canvas = $manager->create($width, $height)->fill('0c0d10');
        $core = $canvas->core()->native();

        $gridColor = imagecolorallocate($core, 22, 25, 32);      // Tech grid lines
        $textColor = imagecolorallocate($core, 35, 40, 26);      // Subtle ABTJOKI watermark pattern
        $headerColor = imagecolorallocate($core, 232, 255, 0);   // Neon Yellow

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

        // 3. Header Bar Info Overlay
        // Left: #XX
        if ($testiNumber) {
            $labelLeft = "#" . ltrim((string)$testiNumber, '#');
            imagestring($core, 5, 24, 18, $labelLeft, $headerColor);
        }

        // Right: Invoice Number
        if (!empty($invoiceNumber)) {
            $invX = $width - (strlen($invoiceNumber) * 10) - 24;
            imagestring($core, 4, max(24, $invX), 18, $invoiceNumber, $headerColor);
        }

        return $canvas;
    }

    /**
     * Wrap an image inside a Neon Yellow Framed Box
     */
    private function frameImage(ImageManager $manager, string $imagePath, int $boxWidth, int $boxHeight, string $bgColor = '13141a')
    {
        $borderSize = 3;
        
        // 1. Outer Box filled with Neon Yellow (#E8FF00)
        $frame = $manager->create($boxWidth, $boxHeight)->fill('e8ff00');
        
        // 2. Inner Dark Box offset by 3px border
        $innerW = max(1, $boxWidth - ($borderSize * 2));
        $innerH = max(1, $boxHeight - ($borderSize * 2));
        $innerBox = $manager->create($innerW, $innerH)->fill($bgColor);
        
        // 3. Contain screenshot inside inner box without cropping
        $img = $manager->read($imagePath)->contain($innerW, $innerH, $bgColor, 'center');
        $innerBox->place($img, 'center');
        
        // 4. Place inner box into neon yellow frame
        $frame->place($innerBox, 'top-left', $borderSize, $borderSize);
        
        return $frame;
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
