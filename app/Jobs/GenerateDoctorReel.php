<?php

namespace App\Jobs;

use App\Models\DoctorReel;
use App\Services\FFmpeg\ReelRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateDoctorReel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public bool $failOnTimeout = true;

    public function __construct(public DoctorReel $doctorReel) {}

    public function handle(ReelRenderer $renderer): void
    {
        Log::info('Video reel job started', [
            'reference_id' => $this->doctorReel->reference_id,
            'attempt' => $this->attempts(),
        ]);
        $this->doctorReel->update(['status' => 'processing', 'processing_started_at' => $this->doctorReel->processing_started_at ?? now()]);
        $output = $renderer->render($this->doctorReel);
        $this->doctorReel->update(['generated_video' => $output, 'status' => 'completed', 'processing_completed_at' => now(), 'error_message' => null]);
        $this->doctorReel->statusHistories()->create(['status' => 'completed', 'message' => 'Teacher\'s Day reel generated']);
        Log::info('Video reel job completed', [
            'reference_id' => $this->doctorReel->reference_id,
            'output_path' => $output,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Video reel job failed', [
            'reference_id' => $this->doctorReel->reference_id,
            'exception' => $exception,
        ]);
        $this->doctorReel->update(['status' => 'failed', 'processing_failed_at' => now(), 'error_message' => $exception?->getMessage()]);
        $this->doctorReel->statusHistories()->create(['status' => 'failed', 'message' => 'Reel generation failed', 'metadata' => ['error' => $exception?->getMessage()]]);
    }
}
