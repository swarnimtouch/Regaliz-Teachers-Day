<?php

namespace App\Services\FFmpeg;

use App\Models\DoctorReel;
use App\Services\Reel\TemplateArtwork;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AudioReelRenderer
{
    public function __construct(private readonly TemplateArtwork $artwork) {}

    public function render(DoctorReel $reel): string
    {
        if (! $reel->original_audio || ! Storage::disk('local')->exists($reel->original_audio)) {
            throw new RuntimeException('The source audio is missing.');
        }

        $template = public_path('videos/teachers-day-animation.mp4');
        if (! is_file($template)) {
            throw new RuntimeException('The Teacher\'s Day animation template is missing.');
        }

        $output = 'reels/'.now()->format('Y/m').'/'.$reel->reference_id.'-audio.mp4';
        Storage::disk('local')->makeDirectory(dirname($output));
        $caption = Storage::disk('local')->path($this->artwork->buildAnimationCaption($reel));
        $duration = $this->mediaDuration(Storage::disk('local')->path($reel->original_audio));
        $filter = "[1:v]split=3[introbase][middlebase][outrobase];[introbase]trim=start=0:end=8.7,setpts=PTS-STARTPTS[intro];[middlebase]trim=start=12:end=12.04,setpts=PTS-STARTPTS,tpad=stop_mode=clone:stop_duration={$duration},trim=duration={$duration}[middle];[outrobase]trim=start=14,setpts=PTS-STARTPTS[outro];[0:a]atrim=duration={$duration},asetpts=PTS-STARTPTS[messageaudio];[messageaudio]asplit=2[voice][waveaudio];[waveaudio]showwaves=s=619x180:mode=cline:rate=30:colors=0xffff00,format=rgba[wave];[middle][2:v]overlay=0:0,trim=duration={$duration}[cleanmiddle];[cleanmiddle][wave]overlay=219:880:eof_action=repeat,trim=duration={$duration}[message];[intro][message][outro]concat=n=3:v=1:a=0,format=yuv420p[v];[1:a]asplit=2[introaudio][outroaudio];[introaudio]atrim=start=0:end=8.7,asetpts=PTS-STARTPTS[ia];[voice]asetpts=PTS-STARTPTS[ma];[outroaudio]atrim=start=14,asetpts=PTS-STARTPTS[oa];[ia][ma][oa]concat=n=3:v=0:a=1[a]";
        $result = Process::timeout(300)->run([
            config('services.ffmpeg.binary', 'ffmpeg'), '-y', '-i', Storage::disk('local')->path($reel->original_audio),
            '-i', $template, '-loop', '1', '-i', $caption, '-filter_complex', $filter,
            '-map', '[v]', '-map', '[a]', '-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '24',
            '-r', '30', '-c:a', 'aac', '-b:a', '192k', '-shortest', '-movflags', '+faststart',
            Storage::disk('local')->path($output),
        ]);

        if ($result->failed()) {
            throw new RuntimeException('FFmpeg audio reel failed: '.mb_substr($result->errorOutput(), -1500));
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
