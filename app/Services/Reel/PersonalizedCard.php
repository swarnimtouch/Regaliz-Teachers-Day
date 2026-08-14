<?php

namespace App\Services\Reel;

use App\Models\DoctorReel;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PersonalizedCard
{
    public function generate(DoctorReel $reel, string $template = 'chalkboard'): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD is required to create cards.');
        }

        $path = 'cards/'.now()->format('Y/m').'/'.$reel->reference_id.'.png';
        Storage::disk('local')->makeDirectory(dirname($path));
        $image = imagecreatetruecolor(1080, 1350);
        [$primary, $accent, $text] = $this->drawBackground($image, $template);
        $font = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arial.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        $bold = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arialbd.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

        if ($template === 'golden') {
            $this->renderCertificate($image, $reel, $primary, $accent, $text, $font, $bold);
        } elseif ($template === 'notebook') {
            $this->renderNotebook($image, $reel, $primary, $accent, $text, $font, $bold);
        } else {
            $this->renderChalkboard($image, $reel, $primary, $accent, $text, $font, $bold);
        }

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

    private function renderChalkboard($image, DoctorReel $reel, int $primary, int $accent, int $text, string $font, string $bold): void
    {
        $this->center($image, "HAPPY TEACHER'S DAY", 55, 190, $accent, $bold);
        $this->center($image, 'Celebrating the mentor who inspires every step', 25, 245, $text, $font);
        imageellipse($image, 540, 485, 250, 250, $accent);
        $this->center($image, 'GURU', 42, 500, $primary, $bold);
        $this->center($image, 'Dear '.$reel->teacher_name.'.', 38, 700, $accent, $bold);
        foreach ($this->wrap($reel->card_message, 38) as $index => $line) {
            $this->center($image, $line, 30, 775 + ($index * 48), $text, $font);
        }
        $this->center($image, 'With gratitude,', 25, 1080, $text, $font);
        $this->center($image, $reel->doctor_name, 37, 1140, $accent, $bold);
        $this->center($image, $reel->city, 22, 1190, $text, $font);
        $this->center($image, 'THE BEST TEACHERS HELP US REACH THE TOP', 20, 1250, $text, $bold);
    }

    private function renderCertificate($image, DoctorReel $reel, int $primary, int $accent, int $text, string $font, string $bold): void
    {
        $this->center($image, 'CERTIFICATE', 64, 210, $accent, $bold);
        $this->center($image, 'OF APPRECIATION', 29, 260, $accent, $bold);
        imageline($image, 260, 295, 820, 295, $accent);
        $this->center($image, 'Proudly presented to', 25, 410, $text, $font);
        $this->center($image, $reel->teacher_name, 48, 500, $accent, $bold);
        $this->center($image, 'for inspiring minds and shaping brighter futures', 23, 555, $text, $font);
        foreach ($this->wrap($reel->card_message, 40) as $index => $line) {
            $this->center($image, $line, 29, 710 + ($index * 46), $text, $font);
        }
        imageellipse($image, 540, 1000, 115, 115, $accent);
        $this->center($image, '*', 48, 1018, $primary, $bold);
        $this->center($image, 'Presented with respect and gratitude by', 22, 1110, $text, $font);
        $this->center($image, $reel->doctor_name, 35, 1170, $accent, $bold);
        $this->center($image, $reel->city, 20, 1210, $text, $font);
    }

    private function renderNotebook($image, DoctorReel $reel, int $primary, int $accent, int $text, string $font, string $bold): void
    {
        $this->left($image, 'A NOTE FOR MY TEACHER', 48, 150, $accent, $bold);
        $this->left($image, 'A+', 58, 260, $accent, $bold);
        $this->left($image, 'Dear '.$reel->teacher_name.',', 38, 410, $text, $bold);
        foreach ($this->wrap($reel->card_message, 36) as $index => $line) {
            $this->left($image, $line, 30, 500 + ($index * 52), $text, $font);
        }
        $this->left($image, 'You made every lesson matter.', 27, 895, $accent, $bold);
        $this->left($image, 'With gratitude,', 24, 1030, $text, $font);
        $this->left($image, $reel->doctor_name, 38, 1090, $accent, $bold);
        $this->left($image, $reel->city, 21, 1132, $text, $font);
        $this->left($image, 'THANK YOU FOR HELPING ME GROW', 20, 1230, $text, $bold);
    }

    private function left($image, string $text, int $size, int $y, int $color, string $font): void
    {
        imagettftext($image, $size, 0, 155, $y, $color, $font, $text);
    }

    private function drawBackground($image, string $template): array
    {
        if ($template === 'golden') {
            $dark = imagecolorallocate($image, 91, 53, 20);
            $gold = imagecolorallocate($image, 220, 168, 55);
            $cream = imagecolorallocate($image, 255, 247, 214);
            imagefill($image, 0, 0, $dark);
            imagefilledrectangle($image, 24, 24, 1055, 1325, $gold);
            imagefilledrectangle($image, 66, 66, 1013, 1283, $cream);
            for ($r = 900; $r > 100; $r -= 90) {
                imageellipse($image, 540, 500, $r, $r, $gold);
            }
            return [$cream, $dark, $dark];
        }

        if ($template === 'notebook') {
            $blue = imagecolorallocate($image, 35, 76, 101);
            $paper = imagecolorallocate($image, 255, 253, 244);
            $red = imagecolorallocate($image, 185, 69, 69);
            $line = imagecolorallocate($image, 183, 208, 222);
            imagefill($image, 0, 0, $blue);
            imagefilledrectangle($image, 28, 28, 1051, 1321, $paper);
            imagefilledrectangle($image, 105, 28, 119, 1321, $red);
            for ($y = 90; $y < 1300; $y += 42) {
                imageline($image, 42, $y, 1037, $y, $line);
            }
            return [$paper, $red, $blue];
        }

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
        return [$green, $yellow, $white];
    }
}
