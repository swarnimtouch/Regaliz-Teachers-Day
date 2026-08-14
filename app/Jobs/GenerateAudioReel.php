<?php

namespace App\Jobs;

use App\Models\DoctorReel;
use App\Services\FFmpeg\AudioReelRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateAudioReel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(public DoctorReel $doctorReel) {}

    public function handle(AudioReelRenderer $renderer): void
    {
        Log::info('Audio reel job started', [
            'reference_id' => $this->doctorReel->reference_id,
            'attempt' => $this->attempts(),
        ]);
        $output = $renderer->render($this->doctorReel);
        $this->doctorReel->audioMessage()->update([
            'generated_video' => $output,
            'status' => 'completed',
            'processing_completed_at' => now(),
            'error_message' => null,
        ]);
        $this->doctorReel->update(['status' => 'completed', 'processing_completed_at' => now(), 'error_message' => null]);
        $this->doctorReel->statusHistories()->create(['status' => 'completed', 'message' => 'Audio message video generated']);
        Log::info('Audio reel job completed', [
            'reference_id' => $this->doctorReel->reference_id,
            'output_path' => $output,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Audio reel job failed', [
            'reference_id' => $this->doctorReel->reference_id,
            'exception' => $exception,
        ]);
        $this->doctorReel->update(['status' => 'failed', 'processing_failed_at' => now(), 'error_message' => $exception?->getMessage()]);
        $this->doctorReel->audioMessage()->update(['status' => 'failed', 'error_message' => $exception?->getMessage()]);
    }
}
