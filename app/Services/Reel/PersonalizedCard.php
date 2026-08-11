<?php

namespace App\Services\Reel;

use App\Models\DoctorReel;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PersonalizedCard
{
    public function generate(DoctorReel $reel): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD is required to create cards.');
        }
        $path = 'cards/'.now()->format('Y/m').'/'.$reel->reference_id.'.png';
        Storage::disk('local')->makeDirectory(dirname($path));
        $image = imagecreatetruecolor(1080, 1350);
        $green = imagecolorallocate($image, 35, 82, 55);
        $greenLight = imagecolorallocate($image, 48, 105, 71);
        $wood = imagecolorallocate($image, 174, 113, 56);
        $woodDark = imagecolorallocate($image, 105, 62, 28);
        $yellow = imagecolorallocate($image, 246, 215, 27);
        $white = imagecolorallocate($image, 255, 253, 238);
        imagefill($image, 0, 0, $woodDark);
        imagefilledrectangle($image, 22, 22, 1057, 1327, $wood);
        imagefilledrectangle($image, 58, 58, 1021, 1291, $green);
        for ($y = 70; $y < 1280; $y += 28) {
            imageline($image, 70, $y, 1010, $y + random_int(-3, 3), $greenLight);
        }
        $font = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arial.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        $bold = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arialbd.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $this->center($image, "HAPPY TEACHER'S DAY", 55, 190, $yellow, $bold);
        $this->center($image, 'A tribute from a grateful student', 26, 245, $white, $font);
        imageellipse($image, 540, 485, 250, 250, $yellow);
        $this->center($image, 'GURU', 42, 500, $white, $bold);
        $this->center($image, 'Dear '.$reel->teacher_name.',', 38, 700, $yellow, $bold);
        $lines = $this->wrap($reel->card_message, 38);
        foreach ($lines as $index => $line) {
            $this->center($image, $line, 30, 775 + ($index * 48), $white, $font);
        }
        $this->center($image, 'With gratitude,', 25, 1080, $white, $font);
        $this->center($image, $reel->doctor_name, 37, 1140, $yellow, $bold);
        $this->center($image, implode('  |  ', array_filter([$reel->speciality, $reel->city])), 22, 1190, $white, $font);
        $this->center($image, 'THE BEST TEACHERS HELP US REACH THE TOP', 20, 1250, $white, $bold);
        $this->placeLogo($image, 760, 1265, 270, 66);
        imagepng($image, Storage::disk('local')->path($path));
        imagedestroy($image);

        return $path;
    }

    private function center($image, string $text, int $size, int $y, int $color, string $font): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        imagettftext($image, $size, 0, (int) ((1080 - ($box[2] - $box[0])) / 2), $y, $color, $font, $text);
    }

    private function wrap(string $text, int $length): array
    {
        return explode("\n", wordwrap($text, $length, "\n", true));
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
