<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'about',
        'speakers',
        'speakers_image',
        'agenda_image',
        'status',
    ];

    protected $casts = [
        'speakers' => 'array',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeModules(): HasMany
    {
        return $this->modules()->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
