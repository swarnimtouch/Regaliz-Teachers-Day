<?php

namespace App\Jobs;

use App\Models\DoctorReel;
use App\Services\FFmpeg\AudioReelRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateAudioReel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(public DoctorReel $doctorReel) {}

    public function handle(AudioReelRenderer $renderer): void
    {
        $output = $renderer->render($this->doctorReel);
        $this->doctorReel->update(['generated_video' => $output, 'status' => 'completed', 'processing_completed_at' => now(), 'error_message' => null]);
        $this->doctorReel->statusHistories()->create(['status' => 'completed', 'message' => 'Audio tribute video generated']);
    }

    public function failed(?Throwable $exception): void
    {
        $this->doctorReel->update(['status' => 'failed', 'processing_failed_at' => now(), 'error_message' => $exception?->getMessage()]);
    }
}
