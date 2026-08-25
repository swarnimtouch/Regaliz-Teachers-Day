<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorReel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['reference_id', 'doctor_name', 'speciality', 'city', 'consent', 'content_type', 'teacher_name', 'card_message', 'original_video', 'original_video_url', 'original_audio', 'original_audio_url', 'video_zoom', 'details_image', 'generated_video', 'generated_video_url', 'generated_card', 'generated_card_url', 'template_id', 'status', 'error_message', 'download_count', 'processing_started_at', 'processing_completed_at', 'processing_failed_at'];

    public function getRouteKeyName(): string
    {
        return 'reference_id';
    }

    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'video_zoom' => 'float',
            'processing_started_at' => 'datetime',
            'processing_completed_at' => 'datetime',
            'processing_failed_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReelTemplate::class, 'template_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReelStatusHistory::class);
    }

    public function audioMessage(): HasOne
    {
        return $this->hasOne(AudioMessage::class);
    }

    public function greetingCard(): HasOne
    {
        return $this->hasOne(GreetingCard::class);
    }
}
