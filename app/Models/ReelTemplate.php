<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReelTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'preview_image', 'background_file', 'foreground_file', 'configuration', 'quote', 'status', 'is_default'];

    protected function casts(): array
    {
        return ['configuration' => 'array', 'status' => 'boolean', 'is_default' => 'boolean'];
    }

    public function doctorReels(): HasMany
    {
        return $this->hasMany(DoctorReel::class, 'template_id');
    }
}
