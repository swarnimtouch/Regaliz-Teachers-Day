<?php

namespace App\Services\FFmpeg;

use App\Models\DoctorReel;
use App\Services\MediaStorage;
use App\Services\Reel\TemplateArtwork;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AudioReelRenderer
{
    public function __construct(private readonly TemplateArtwork $artwork, private readonly MediaStorage $media) {}

    public function render(DoctorReel $reel): string
    {
        if (! $reel->original_audio || ! $this->media->disk()->exists($reel->original_audio)) {
            throw new RuntimeException('The source audio is missing.');
        }

        $output = $this->media->path('audios/'.$reel->reference_id.'.mp4');
        $inputPath = $this->media->localPath($reel->original_audio);
        $outputPath = $this->media->outputPath($output);
        $banner = Storage::disk('local')->path($this->artwork->buildAudioBanner($reel));
        $waveVideo = public_path('videos/audio-wave-green-screen.mp4');
        if (! is_file($waveVideo)) {
            throw new RuntimeException('The audio wave animation is missing.');
        }
        $duration = $this->mediaDuration($inputPath);
        $filter = "[0:a]atrim=duration={$duration},asetpts=PTS-STARTPTS[voice];[1:v]scale=1080:1920,setsar=1,trim=duration={$duration},setpts=PTS-STARTPTS[board];[2:v]crop=1920:420:0:300,scale=780:110,format=rgba,colorkey=0x2fa83d:0.30:0.10,trim=duration={$duration},setpts=PTS-STARTPTS[wave];[board][wave]overlay=150:1645:shortest=1,format=yuv420p[v]";
        $result = Process::timeout(300)->run([
            config('services.ffmpeg.binary', 'ffmpeg'), '-y', '-i', $inputPath,
            '-loop', '1', '-framerate', '30', '-i', $banner, '-stream_loop', '-1', '-i', $waveVideo, '-filter_complex', $filter,
            '-map', '[v]', '-map', '[voice]', '-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '24',
            '-r', '30', '-c:a', 'aac', '-b:a', '192k', '-shortest', '-movflags', '+faststart',
            $outputPath,
        ]);

        if ($result->failed()) {
            throw new RuntimeException('FFmpeg audio reel failed: '.mb_substr($result->errorOutput(), -1500));
        }

        $this->media->publish($outputPath, $output);
        $this->media->cleanupLocalCopy($inputPath);

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
