<?php

namespace App\Services\Reel;

use App\Models\DoctorReel;
use App\Services\MediaStorage;
use RuntimeException;

class PersonalizedCard
{
    public function __construct(private readonly MediaStorage $media) {}

    public function saveRendered(DoctorReel $reel, string $dataUrl): string
    {
        if (! preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=]+)$/', $dataUrl, $matches)) {
            throw new RuntimeException('The rendered card must be a PNG image.');
        }

        $contents = base64_decode($matches[1], true);
        $details = $contents === false ? false : getimagesizefromstring($contents);

        if ($contents === false || strlen($contents) > 8 * 1024 * 1024 || $details === false || $details[2] !== IMAGETYPE_PNG) {
            throw new RuntimeException('The rendered card image is invalid.');
        }

        if ($details[0] !== 1080 || abs($details[1] - 1620) > 2) {
            throw new RuntimeException('The rendered card has invalid dimensions.');
        }

        $path = $this->media->path('cards/'.$reel->reference_id.'.png');
        $this->media->disk()->put($path, $contents);

        return $path;
    }

    public function generate(DoctorReel $reel, string $template = 'chalkboard'): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD is required to create cards.');
        }

        $path = $this->media->path('cards/'.$reel->reference_id.'.png');
        $outputPath = $this->media->outputPath($path);
        $height = 1620;
        $image = imagecreatetruecolor(1080, $height);
        [$primary, $accent, $text] = $this->drawBackground($image, $template);
        $font = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arial.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        $bold = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\arialbd.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $italic = PHP_OS_FAMILY === 'Windows' && is_file('C:\\Windows\\Fonts\\ariali.ttf')
            ? 'C:\\Windows\\Fonts\\ariali.ttf'
            : $font;

        if ($template === 'golden') {
            $this->renderCertificate($image, $reel, $primary, $accent, $text, $italic, $bold);
        } elseif ($template === 'notebook') {
            $this->renderNotebook($image, $reel, $primary, $accent, $text, $font, $bold);
        } else {
            $this->renderChalkboard($image, $reel, $primary, $accent, $text, $font, $bold);
        }

        imagepng($image, $outputPath);
        imagedestroy($image);
        $this->media->publish($outputPath, $path);

        return $path;
    }

    private function center($image, string $text, int $size, int $y, int $color, string $font): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        imagettftext($image, $size, 0, (int) ((1080 - ($box[2] - $box[0])) / 2), $y, $color, $font, $text);
    }

    private function wrapToWidth(string $text, int $size, string $font, int $maxWidth = 760): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            $box = imagettfbbox($size, 0, $font, $candidate);
            $width = $box === false ? PHP_INT_MAX : abs($box[2] - $box[0]);

            if ($line !== '' && $width > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines ?: [''];
    }

    private function renderChalkboard($image, DoctorReel $reel, int $primary, int $accent, int $text, string $font, string $bold): void
    {
        $serifBold = PHP_OS_FAMILY === 'Windows' && is_file('C:\\Windows\\Fonts\\timesbd.ttf') ? 'C:\\Windows\\Fonts\\timesbd.ttf' : (is_file('/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf') ? '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf' : $bold);
        $serifBoldItalic = PHP_OS_FAMILY === 'Windows' && is_file('C:\\Windows\\Fonts\\timesbi.ttf') ? 'C:\\Windows\\Fonts\\timesbi.ttf' : (is_file('/usr/share/fonts/truetype/dejavu/DejaVuSerif-BoldItalic.ttf') ? '/usr/share/fonts/truetype/dejavu/DejaVuSerif-BoldItalic.ttf' : $font);
        $this->center($image, 'Dear '.$reel->teacher_name.',', 48, 640, $accent, $serifBold);
        $messageLength = function_exists('mb_strlen') ? mb_strlen($reel->card_message) : strlen($reel->card_message);
        [$messageSize, $lineHeight] = match (true) {
            $messageLength <= 45 => [46, 58],
            $messageLength <= 90 => [40, 50],
            $messageLength <= 150 => [34, 43],
            $messageLength <= 200 => [28, 35],
            default => [24, 30],
        };

        $messageLines = $this->wrapToWidth($reel->card_message, $messageSize, $serifBoldItalic);
        $blockSpan = (count($messageLines) - 1) * $lineHeight;
        $startY = (int) (810 - ($blockSpan / 2) + ($messageSize / 3));

        foreach ($messageLines as $index => $line) {
            $this->center($image, $line, $messageSize, $startY + ($index * $lineHeight), $text, $serifBoldItalic);
        }
        $this->center($image, 'WITH GRATITUDE,', 24, 995, $text, $bold);
        $this->center($image, $reel->doctor_name, 40, 1050, $accent, $serifBold);
        $this->center($image, $reel->city, 29, 1105, $text, $font);
    }

    private function renderCertificate($image, DoctorReel $reel, int $primary, int $accent, int $text, string $font, string $bold): void
    {
        $serifBold = PHP_OS_FAMILY === 'Windows' && is_file('C:\\Windows\\Fonts\\timesbd.ttf') ? 'C:\\Windows\\Fonts\\timesbd.ttf' : (is_file('/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf') ? '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf' : $bold);
        $serifBoldItalic = PHP_OS_FAMILY === 'Windows' && is_file('C:\\Windows\\Fonts\\timesbi.ttf') ? 'C:\\Windows\\Fonts\\timesbi.ttf' : (is_file('/usr/share/fonts/truetype/dejavu/DejaVuSerif-BoldItalic.ttf') ? '/usr/share/fonts/truetype/dejavu/DejaVuSerif-BoldItalic.ttf' : $font);
        $this->center($image, 'Dear '.$reel->teacher_name.',', 48, 640, $accent, $serifBold);
        $messageLength = function_exists('mb_strlen') ? mb_strlen($reel->card_message) : strlen($reel->card_message);
        [$messageSize, $lineHeight] = match (true) {
            $messageLength <= 45 => [46, 58],
            $messageLength <= 90 => [40, 50],
            $messageLength <= 150 => [34, 43],
            $messageLength <= 200 => [28, 35],
            default => [24, 30],
        };
        $messageLines = $this->wrapToWidth($reel->card_message, $messageSize, $serifBoldItalic);
        $blockSpan = (count($messageLines) - 1) * $lineHeight;
        $startY = (int) (820 - ($blockSpan / 2) + ($messageSize / 3));

        foreach ($messageLines as $index => $line) {
            $this->center($image, $line, $messageSize, $startY + ($index * $lineHeight), $text, $serifBoldItalic);
        }
        $this->center($image, 'WITH GRATITUDE,', 24, 1015, $text, $bold);
        $this->center($image, $reel->doctor_name, 40, 1085, $accent, $serifBold);
        $this->center($image, $reel->city, 28, 1115, $text, $font);
    }

    private function renderNotebook($image, DoctorReel $reel, int $primary, int $accent, int $text, string $font, string $bold): void
    {
        $serifBold = PHP_OS_FAMILY === 'Windows' && is_file('C:\\Windows\\Fonts\\timesbd.ttf') ? 'C:\\Windows\\Fonts\\timesbd.ttf' : (is_file('/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf') ? '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf' : $bold);
        $serifBoldItalic = PHP_OS_FAMILY === 'Windows' && is_file('C:\\Windows\\Fonts\\timesbi.ttf') ? 'C:\\Windows\\Fonts\\timesbi.ttf' : (is_file('/usr/share/fonts/truetype/dejavu/DejaVuSerif-BoldItalic.ttf') ? '/usr/share/fonts/truetype/dejavu/DejaVuSerif-BoldItalic.ttf' : $font);
        $this->center($image, 'Dear '.$reel->teacher_name.',', 48, 640, $accent, $serifBold);
        $messageLength = function_exists('mb_strlen') ? mb_strlen($reel->card_message) : strlen($reel->card_message);
        [$messageSize, $lineHeight] = match (true) {
            $messageLength <= 45 => [46, 58],
            $messageLength <= 90 => [40, 50],
            $messageLength <= 150 => [34, 43],
            $messageLength <= 200 => [28, 35],
            default => [24, 30],
        };
        $messageLines = $this->wrapToWidth($reel->card_message, $messageSize, $serifBoldItalic);
        $blockSpan = (count($messageLines) - 1) * $lineHeight;
        $startY = (int) (820 - ($blockSpan / 2) + ($messageSize / 3));

        foreach ($messageLines as $index => $line) {
            $this->center($image, $line, $messageSize, $startY + ($index * $lineHeight), $text, $serifBoldItalic);
        }
        $this->center($image, 'WITH GRATITUDE,', 24, 1020, $text, $bold);
        $this->center($image, $reel->doctor_name, 40, 1080, $accent, $serifBold);
        $this->center($image, $reel->city, 27, 1140, $text, $font);
    }

    private function left($image, string $text, int $size, int $y, int $color, string $font): void
    {
        imagettftext($image, $size, 0, 155, $y, $color, $font, $text);
    }

    private function drawBackground($image, string $template): array
    {
        if ($template === 'golden') {
            $dark = imagecolorallocate($image, 91, 53, 20);
            $cream = imagecolorallocate($image, 255, 247, 214);
            $templatePath = public_path('images/golden-card-template-v3.png');
            $background = is_file($templatePath) ? imagecreatefrompng($templatePath) : false;

            if ($background !== false) {
                imagecopyresampled($image, $background, 0, 0, 0, 0, 1080, 1620, imagesx($background), imagesy($background));
                imagedestroy($background);
            } else {
                imagefill($image, 0, 0, $cream);
            }

            return [$cream, $dark, $dark];
        }

        if ($template === 'notebook') {
            $blue = imagecolorallocate($image, 35, 76, 101);
            $paper = imagecolorallocate($image, 255, 253, 244);
            $templatePath = public_path('images/notebook-card-template-v2.png');
            $background = is_file($templatePath) ? imagecreatefrompng($templatePath) : false;

            if ($background !== false) {
                imagecopyresampled($image, $background, 0, 0, 0, 0, 1080, 1620, imagesx($background), imagesy($background));
                imagedestroy($background);
            } else {
                imagefill($image, 0, 0, $paper);
            }

            return [$paper, $blue, $blue];
        }

        $green = imagecolorallocate($image, 21, 77, 45);
        $yellow = imagecolorallocate($image, 246, 215, 27);
        $white = imagecolorallocate($image, 255, 253, 238);
        $templatePath = public_path('images/blackboard-card-template-v3.png');
        $background = is_file($templatePath) ? imagecreatefrompng($templatePath) : false;

        if ($background !== false) {
            imagecopyresampled($image, $background, 0, 0, 0, 0, 1080, 1620, imagesx($background), imagesy($background));
            imagedestroy($background);
        } else {
            imagefill($image, 0, 0, $green);
        }

        return [$green, $yellow, $white];
    }
}
