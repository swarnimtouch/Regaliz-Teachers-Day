<?php

namespace App\Services\Reel;

use App\Models\DoctorReel;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TemplateArtwork
{
    public function buildAudioBanner(DoctorReel $reel): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD is required to create reel artwork.');
        }

        $source = public_path('images/holding-banner-audio.png');
        if (! is_file($source)) {
            throw new RuntimeException('The audio holding banner is missing.');
        }

        $directory = 'reel-artwork/'.now()->format('Y/m');
        Storage::disk('local')->makeDirectory($directory);
        $path = $directory.'/'.$reel->reference_id.'-audio-banner.png';
        // Some exported artwork contains a harmless malformed ICC profile.
        // GD emits a warning for it, which Laravel converts into an exception.
        $image = @imagecreatefrompng($source);
        if ($image === false) {
            throw new RuntimeException('The audio holding banner could not be opened.');
        }
        imagealphablending($image, true);

        $reel->loadMissing('doctor');
        $senderName = trim((string) $reel->doctor?->name);
        if ($senderName === '') {
            throw new RuntimeException('The registered doctor name is missing.');
        }

        // Clear the baked-in "From" placeholder and redraw it with the registered user's name.
        imagecopy($image, $image, 135, 1738, 135, 1628, 700, 72);
        $white = imagecolorallocate($image, 255, 255, 255);
        $fontSize = mb_strlen($senderName) > 28 ? 19 : (mb_strlen($senderName) > 20 ? 21 : 24);
        imagettftext($image, $fontSize, 0, 142, 1810, $white, $this->boldFont(), 'From, '.$senderName);

        imagepng($image, Storage::disk('local')->path($path));
        imagedestroy($image);
        $reel->update(['details_image' => $path]);

        return $path;
    }

    public function buildAnimationCaption(DoctorReel $reel): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD is required to create reel artwork.');
        }

        $directory = 'reel-artwork/'.now()->format('Y/m');
        Storage::disk('local')->makeDirectory($directory);
        $path = $directory.'/'.$reel->reference_id.'-animation-caption.png';
        $image = imagecreatetruecolor(1080, 1920);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagealphablending($image, true);

        // Hide the sample doctor photo and caption baked into the animation.
        // The submitted video or audio waveform is rendered over this clean box.
        $board = imagecolorallocate($image, 67, 119, 84);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 219, 687, 838, 1276, $board);
        imagefilledrectangle($image, 145, 1360, 935, 1605, $board);
        $font = $this->font();
        $nameSize = mb_strlen($reel->doctor_name) > 24 ? 40 : 48;
        $this->centerText($image, $reel->doctor_name, $nameSize, 1435, $white, $font);
        $this->centerText($image, $reel->city, 31, 1525, $white, $font);
        imagepng($image, Storage::disk('local')->path($path));
        imagedestroy($image);

        $reel->update(['details_image' => $path]);

        return $path;
    }

    public function build(DoctorReel $reel): array
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD is required to create reel artwork.');
        }

        $directory = 'reel-artwork/'.now()->format('Y/m');
        Storage::disk('local')->makeDirectory($directory);
        $backgroundPath = $directory.'/'.$reel->reference_id.'-background.png';
        $foregroundPath = $directory.'/'.$reel->reference_id.'-foreground.png';
        $maskPath = $directory.'/'.$reel->reference_id.'-circle-mask.png';

        $this->createBackground(Storage::disk('local')->path($backgroundPath));
        $this->createForeground($reel, Storage::disk('local')->path($foregroundPath));
        $this->createCircleMask(Storage::disk('local')->path($maskPath));
        $reel->update(['details_image' => $foregroundPath]);

        return [$backgroundPath, $foregroundPath, $maskPath];
    }

    private function createBackground(string $path): void
    {
        $image = imagecreatetruecolor(1080, 1920);
        $cream = imagecolorallocate($image, 255, 249, 232);
        $purple = imagecolorallocate($image, 74, 38, 105);
        $gold = imagecolorallocate($image, 238, 183, 58);
        $orange = imagecolorallocate($image, 240, 79, 18);
        imagefill($image, 0, 0, $cream);
        imagefilledellipse($image, 900, -70, 520, 280, $orange);
        imagefilledellipse($image, 45, 20, 300, 170, $purple);
        imagefilledellipse($image, 950, 1800, 500, 320, $gold);
        imagefilledrectangle($image, 0, 0, 1080, 22, $gold);

        $font = $this->font();
        $this->centerText($image, 'HAPPY', 36, 95, $gold, $font);
        $this->centerText($image, "TEACHER'S DAY", 64, 210, $purple, $font);
        $this->centerText($image, 'A heartfelt tribute to those who inspire us', 28, 335, $purple, $font);
        $this->placeLogo($image, 745, 1805, 285, 70);
        imagepng($image, $path);
        imagedestroy($image);
    }

    private function createForeground(DoctorReel $reel, string $path): void
    {
        $image = imagecreatetruecolor(1080, 1920);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        imagealphablending($image, true);
        $orange = imagecolorallocate($image, 240, 79, 18);
        $gold = imagecolorallocate($image, 238, 183, 58);
        $purple = imagecolorallocate($image, 62, 27, 87);
        $muted = imagecolorallocate($image, 103, 84, 112);
        $cream = imagecolorallocate($image, 255, 249, 232);

        // Cover the square video's corners once in the static artwork instead
        // of calculating a circular alpha mask for every FFmpeg frame.
        for ($y = 500; $y < 1300; $y++) {
            for ($x = 140; $x < 940; $x++) {
                if ((($x - 540) ** 2) + (($y - 900) ** 2) > 160000) {
                    imagesetpixel($image, $x, $y, $cream);
                }
            }
        }

        imagesetthickness($image, 14);
        imageellipse($image, 540, 900, 824, 824, $gold);
        imagesetthickness($image, 5);
        imagearc($image, 540, 900, 854, 854, 205, 335, $orange);
        imagearc($image, 540, 900, 854, 854, 25, 155, $orange);

        $font = $this->font();
        $nameSize = mb_strlen($reel->doctor_name) > 24 ? 43 : 52;
        $this->centerText($image, $reel->doctor_name, $nameSize, 1435, $purple, $font);
        $details = $reel->city;
        $this->centerText($image, $details, 30, 1515, $muted, $font);
        $this->centerText($image, 'Thank you for inspiring every generation', 29, 1715, $purple, $font);
        imagepng($image, $path);
        imagedestroy($image);
    }

    private function centerText($image, string $text, int $size, int $y, int $color, string $font): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $width = $box[2] - $box[0];
        imagettftext($image, $size, 0, (int) ((1080 - $width) / 2), $y, $color, $font, $text);
    }

    private function createCircleMask(string $path): void
    {
        $mask = imagecreatetruecolor(800, 800);
        $black = imagecolorallocate($mask, 0, 0, 0);
        $white = imagecolorallocate($mask, 255, 255, 255);
        imagefill($mask, 0, 0, $black);
        imagefilledellipse($mask, 400, 400, 800, 800, $white);
        imagepng($mask, $path);
        imagedestroy($mask);
    }

    private function font(): string
    {
        $font = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arial.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        if (! is_file($font)) {
            throw new RuntimeException('A TrueType font is required to create reel artwork.');
        }

        return $font;
    }

    private function boldFont(): string
    {
        $font = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arialbd.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        if (! is_file($font)) {
            return $this->font();
        }

        return $font;
    }

    private function placeLogo($image, int $x, int $y, int $width, int $height): void
    {
        $path = public_path('images/regaliz-logo.png');
        if (! is_file($path)) {
            return;
        }
        $logo = imagecreatefrompng($path);
        imagecopyresampled($image, $logo, $x, $y, 0, 0, $width, $height, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }
}
