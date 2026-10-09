<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleUserProgress extends Model
{
    protected $table = 'module_user_progress';

    protected $fillable = [
        'module_id',
        'user_id',
        'video_completed_at',
        'quiz_completed_at',
        'quiz_score',
        'quiz_total',
        'progress_percent',
        'answers',
    ];

    protected $casts = [
        'video_completed_at' => 'datetime',
        'quiz_completed_at' => 'datetime',
        'answers' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recalculate(): void
    {
        $this->progress_percent = $this->video_completed_at ? 100 : 0;
    }
}
