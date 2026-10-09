<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $fillable = [
        'module_id',
        'question',
        'question_type',
        'sort_order',
        'status',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function correctOption()
    {
        return $this->hasOne(QuizOption::class)->where('is_correct', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
