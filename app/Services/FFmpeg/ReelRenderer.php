<?php

namespace App\Services\FFmpeg;

use App\Models\DoctorReel;
use App\Services\Reel\TemplateArtwork;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ReelRenderer
{
    public function __construct(private readonly TemplateArtwork $artwork) {}

    public function render(DoctorReel $reel): string
    {
        if (! $reel->original_video || ! Storage::disk('local')->exists($reel->original_video)) {
            throw new RuntimeException('The source recording is missing.');
        }

        $output = 'reels/'.now()->format('Y/m').'/'.$reel->reference_id.'.mp4';
        Storage::disk('local')->makeDirectory(dirname($output));
        $inputPath = Storage::disk('local')->path($reel->original_video);
        $outputPath = Storage::disk('local')->path($output);
        $templatePath = public_path('videos/teachers-day-animation.mp4');
        if (! is_file($templatePath)) {
            throw new RuntimeException('The Teacher\'s Day animation template is missing.');
        }
        $captionPath = Storage::disk('local')->path($this->artwork->buildAnimationCaption($reel));
        $duration = $this->mediaDuration($inputPath);
        $zoom = max(1.00, min(1.50, (float) ($reel->video_zoom ?: 1)));
        $videoWidth = (int) round(619 * $zoom);
        $videoHeight = (int) round(589 * $zoom);
        $cropX = (int) floor(($videoWidth - 619) / 2);
        $cropY = (int) floor(($videoHeight - 589) / 2);
        $filter = "[1:v]split=3[introbase][middlebase][outrobase];[introbase]trim=start=0:end=8.7,setpts=PTS-STARTPTS[intro];[middlebase]trim=start=12:end=12.04,setpts=PTS-STARTPTS,tpad=stop_mode=clone:stop_duration={$duration},trim=duration={$duration}[middle];[outrobase]trim=start=14,setpts=PTS-STARTPTS[outro];[0:v]hflip,scale={$videoWidth}:{$videoHeight}:force_original_aspect_ratio=increase,crop=619:589:{$cropX}:{$cropY},trim=duration={$duration},setpts=PTS-STARTPTS[doctor];[middle][2:v]overlay=0:0,trim=duration={$duration}[cleanmiddle];[cleanmiddle][doctor]overlay=219:687:eof_action=repeat,trim=duration={$duration}[message];[intro][message][outro]concat=n=3:v=1:a=0,format=yuv420p[v];[1:a]asplit=2[introaudio][outroaudio];[introaudio]atrim=start=0:end=8.7,asetpts=PTS-STARTPTS[ia];[0:a]atrim=duration={$duration},asetpts=PTS-STARTPTS[messageaudio];[outroaudio]atrim=start=14,asetpts=PTS-STARTPTS[oa];[ia][messageaudio][oa]concat=n=3:v=0:a=1[a]";

        $result = Process::timeout(300)->run([
            config('services.ffmpeg.binary', 'ffmpeg'), '-y', '-i', $inputPath, '-i', $templatePath,
            '-loop', '1', '-i', $captionPath, '-filter_complex', $filter, '-map', '[v]', '-map', '[a]',
            '-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '24', '-r', '30',
            '-c:a', 'aac', '-b:a', '192k', '-ar', '48000', '-movflags', '+faststart',
            '-shortest', $outputPath,
        ]);

        if ($result->failed()) {
            throw new RuntimeException('FFmpeg failed: '.mb_substr($result->errorOutput(), -1500));
        }

        return $output;
    }

    private function mediaDuration(string $path): float
    {
        $ffmpeg = config('services.ffmpeg.binary', 'ffmpeg');
        $probe = str_contains($ffmpeg, DIRECTORY_SEPARATOR)
            ? dirname($ffmpeg).DIRECTORY_SEPARATOR.(PHP_OS_FAMILY === 'Windows' ? 'ffprobe.exe' : 'ffprobe')
            : 'ffprobe';
        $result = Process::timeout(30)->run([$probe, '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', $path]);
        $duration = (float) trim($result->output());
        if ($result->failed() || $duration <= 0) {
            throw new RuntimeException('Unable to determine the recording duration.');
        }

        return round(min(20, $duration), 3);
    }
}
