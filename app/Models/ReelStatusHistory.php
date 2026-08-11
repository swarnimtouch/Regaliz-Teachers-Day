<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReelStatusHistory extends Model
{
    protected $fillable = ['doctor_reel_id', 'status', 'message', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function doctorReel(): BelongsTo
    {
        return $this->belongsTo(DoctorReel::class);
    }
}
