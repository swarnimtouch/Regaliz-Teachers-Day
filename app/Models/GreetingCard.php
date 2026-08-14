<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GreetingCard extends Model
{
    protected $fillable = ['doctor_reel_id', 'teacher_name', 'message', 'generated_card', 'status', 'processing_completed_at'];

    protected function casts(): array
    {
        return ['processing_completed_at' => 'datetime'];
    }

    public function doctorReel(): BelongsTo
    {
        return $this->belongsTo(DoctorReel::class);
    }
}
