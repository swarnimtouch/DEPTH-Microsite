<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Speciality extends Model
{
    public function getImageAttribute($value)
    {
        if (!$value || !Storage::disk('public')->exists($value)) {
            return asset('assets/media/no_image.png');
        }

        return Storage::disk('public')->url($value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'doctor_specialities', 'speciality_id', 'doctor_id');
    }
}
