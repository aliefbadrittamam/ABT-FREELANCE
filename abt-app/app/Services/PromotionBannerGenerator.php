<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PromotionBannerGenerator
{
    /**
     * Generate an aesthetic Dark-Neon Promotional Banner (1080x1080).
     *
     * @param string $title Main promotional headline
     * @param string|null $categoryName Service category name
     * @param string|null $tagline Subtitle / key highlights
     * @param string $outputPath Output file destination
     * @return string
     */
    public function generate(string $title, ?string $categoryName, ?string $tagline, string $outputPath): string
    {
        $manager = new ImageManager(new Driver());
        $width = 1080;
        $height = 1080;

        $canvas = $manager->create($width, $height)->fill('0c0d10');
        $core = $canvas->core()->native();

        $gridColor = imagecolorallocate($core, 22, 25, 32);
        $textColor = imagecolorallocate($core, 35, 40, 26);
        $neonYellow = imagecolorallocate($core, 232, 255, 0); // #e8ff00
        $pureWhite = imagecolorallocate($core, 255, 255, 255);
        $graySub = imagecolorallocate($core, 180, 185, 195);
        $darkBoxBg = imagecolorallocate($core, 17, 19, 24);

        // 1. Tech Grid Lines
        for ($x = 0; $x <= $width; $x += 60) imageline($core, $x, 0, $x, $height, $gridColor);
        for ($y = 0; $y <= $height; $y += 60) imageline($core, 0, $y, $width, $y, $gridColor);

        // 2. Subtle Staggered Watermark Pattern
        $watermark = "ABTJOKI";
        for ($y = 15; $y < $height; $y += 65) {
            $shift = (($y / 65) % 2 === 0) ? 10 : 70;
            for ($x = $shift; $x < $width; $x += 140) {
                imagestring($core, 4, $x, $y, $watermark, $textColor);
            }
        }

        // 3. Outer Neon Yellow Frame Box
        $pad = 36;
        $borderThick = 3;
        for ($b = 0; $b < $borderThick; $b++) {
            imagerectangle($core, $pad - $b, $pad - $b, $width - $pad + $b, $height - $pad + $b, $neonYellow);
        }

        // 4. Inner Header Bar
        $headerCat = strtoupper($categoryName ?: 'ABT FREELANCE SERVICES');
        imagestring($core, 5, $pad + 24, $pad + 24, "⚡ " . $headerCat, $neonYellow);

        $brandRight = "ABT-FREELANCE";
        $brandX = $width - $pad - (strlen($brandRight) * 10) - 24;
        imagestring($core, 5, $brandX, $pad + 24, $brandRight, $neonYellow);

        // Divider Line under Header
        imageline($core, $pad + 20, $pad + 54, $width - $pad - 20, $pad + 54, $neonYellow);

        // 5. Center Banner Box Card
        $cardX1 = $pad + 35;
        $cardY1 = $pad + 85;
        $cardX2 = $width - $pad - 35;
        $cardY2 = $height - $pad - 120;

        imagefilledrectangle($core, $cardX1, $cardY1, $cardX2, $cardY2, $darkBoxBg);
        imagerectangle($core, $cardX1, $cardY1, $cardX2, $cardY2, $neonYellow);

        // Decorative Neon Corner Accents
        $cornerLen = 25;
        // Top-left
        imagefilledrectangle($core, $cardX1, $cardY1, $cardX1 + $cornerLen, $cardY1 + 4, $neonYellow);
        imagefilledrectangle($core, $cardX1, $cardY1, $cardX1 + 4, $cardY1 + $cornerLen, $neonYellow);
        // Top-right
        imagefilledrectangle($core, $cardX2 - $cornerLen, $cardY1, $cardX2, $cardY1 + 4, $neonYellow);
        imagefilledrectangle($core, $cardX2 - 4, $cardY1, $cardX2, $cardY1 + $cornerLen, $neonYellow);
        // Bottom-left
        imagefilledrectangle($core, $cardX1, $cardY2 - 4, $cardX1 + $cornerLen, $cardY2, $neonYellow);
        imagefilledrectangle($core, $cardX1, $cardY2 - $cornerLen, $cardX1 + 4, $cardY2, $neonYellow);
        // Bottom-right
        imagefilledrectangle($core, $cardX2 - $cornerLen, $cardY2 - 4, $cardX2, $cardY2, $neonYellow);
        imagefilledrectangle($core, $cardX2 - 4, $cardY2 - $cornerLen, $cardX2, $cardY2, $neonYellow);

        // 6. Draw Big Scaled Headline Title
        $titleText = strtoupper($title);
        $titleSubCanvas = $manager->create(400, 70)->fill('000000');
        $titleCore = $titleSubCanvas->core()->native();
        imagecolortransparent($titleCore, imagecolorallocate($titleCore, 0, 0, 0));
        $titleColor = imagecolorallocate($titleCore, 232, 255, 0);

        // Word wrap title if long
        $words = explode(' ', $titleText);
        $line1 = implode(' ', array_slice($words, 0, 4));
        $line2 = implode(' ', array_slice($words, 4));

        imagestring($titleCore, 5, 10, 10, $line1, $titleColor);
        if (!empty($line2)) {
            imagestring($titleCore, 5, 10, 35, $line2, $titleColor);
        }

        // Scale title text up for high visual impact
        $scaledTitleW = (int)($cardX2 - $cardX1 - 80);
        $scaledTitleH = (int)($scaledTitleW * 0.22);
        $titleSubCanvas->scale($scaledTitleW, $scaledTitleH);

        $canvas->place($titleSubCanvas, 'top-left', $cardX1 + 40, $cardY1 + 40);

        // 7. Draw Tagline & Highlight Points
        $taglineY = $cardY1 + $scaledTitleH + 70;
        if (!empty($tagline)) {
            $taglines = explode('•', $tagline);
            foreach ($taglines as $idx => $point) {
                $pointClean = trim($point);
                if (empty($pointClean)) continue;

                $bulletY = $taglineY + ($idx * 55);
                if ($bulletY > $cardY2 - 80) break;

                // Bullet Icon & Text
                imagestring($core, 5, $cardX1 + 45, $bulletY, "✔", $neonYellow);
                imagestring($core, 5, $cardX1 + 80, $bulletY, $pointClean, $pureWhite);
            }
        } else {
            // Default high-converting trust badges
            $defaultPoints = [
                "Pengerjaan Cepat, Rapi & Terstruktur",
                "100% Bebas Plagiasi Turnitin & Sesuai Panduan",
                "Garansi Revisi Sampai ACC & Tuntas",
                "Privasi & Kerahasiaan Data Mahasiswa Terjamin",
            ];
            foreach ($defaultPoints as $idx => $point) {
                $bulletY = $taglineY + ($idx * 55);
                imagestring($core, 5, $cardX1 + 45, $bulletY, "✔", $neonYellow);
                imagestring($core, 5, $cardX1 + 80, $bulletY, $point, $pureWhite);
            }
        }

        // 8. Place Center Logo Icon if exists
        $logoPath = storage_path('app/public/assets/logo.png');
        if (!file_exists($logoPath)) $logoPath = base_path('logo.png');

        if (file_exists($logoPath)) {
            $logo = $manager->read($logoPath);
            $logo->scaleDown(70, 70);
            $canvas->place($logo, 'top-left', (int)(($width - 70) / 2), $cardY2 - 110);
        }

        // 9. Bottom Call to Action Footer
        $ctaBoxY1 = $height - $pad - 90;
        $ctaBoxY2 = $height - $pad - 15;
        imagefilledrectangle($core, $cardX1, $ctaBoxY1, $cardX2, $ctaBoxY2, $neonYellow);

        $ctaText = "KONSULTASI & ORDER SEKARANG VIA WHATSAPP";
        $ctaX = (int)(($width - (strlen($ctaText) * 9)) / 2);
        $darkText = imagecolorallocate($core, 15, 18, 24);
        imagestring($core, 5, $ctaX, $ctaBoxY1 + 24, $ctaText, $darkText);

        $dir = dirname($outputPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $canvas->toJpeg(95)->save($outputPath);

        return $outputPath;
    }
}
