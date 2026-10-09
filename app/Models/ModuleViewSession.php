<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleViewSession extends Model
{
    protected $fillable = [
        'module_id',
        'user_id',
        'session_id',
        'started_at',
        'last_seen_at',
        'total_seconds',
        'during_event_seconds',
        'post_event_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'total_seconds' => 'integer',
        'during_event_seconds' => 'integer',
        'post_event_seconds' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
