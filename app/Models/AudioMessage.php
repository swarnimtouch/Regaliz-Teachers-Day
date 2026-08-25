<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioMessage extends Model
{
    protected $fillable = ['doctor_reel_id', 'original_audio', 'original_audio_url', 'generated_video', 'generated_video_url', 'status', 'error_message', 'processing_started_at', 'processing_completed_at'];

    protected function casts(): array
    {
        return ['processing_started_at' => 'datetime', 'processing_completed_at' => 'datetime'];
    }

    public function doctorReel(): BelongsTo
    {
        return $this->belongsTo(DoctorReel::class);
    }
}
