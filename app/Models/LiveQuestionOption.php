<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveQuestionOption extends Model
{
    protected $fillable = ['option_text', 'is_correct', 'sort_order'];
    protected $casts = ['is_correct' => 'boolean'];
    public function question(): BelongsTo { return $this->belongsTo(LiveQuestion::class, 'live_question_id'); }
    public function responses(): HasMany { return $this->hasMany(LiveQuestionResponse::class); }
}
