<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveQuestion extends Model
{
    protected $fillable = ['module_id', 'created_by', 'type', 'question', 'status', 'started_at', 'stopped_at'];

    protected $casts = ['started_at' => 'datetime', 'stopped_at' => 'datetime'];

    public function module(): BelongsTo { return $this->belongsTo(Module::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function options(): HasMany { return $this->hasMany(LiveQuestionOption::class)->orderBy('sort_order')->orderBy('id'); }
    public function responses(): HasMany { return $this->hasMany(LiveQuestionResponse::class); }
    public function logs(): HasMany { return $this->hasMany(LiveQuestionLog::class); }
}
