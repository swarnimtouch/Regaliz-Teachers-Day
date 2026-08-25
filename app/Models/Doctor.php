<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = ['name', 'speciality', 'city'];

    public function reels(): HasMany
    {
        return $this->hasMany(DoctorReel::class);
    }
}
