<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQuestionResponse extends Model
{
    protected $fillable = ['live_question_id', 'live_question_option_id', 'user_id', 'is_correct', 'answered_at'];
    protected $casts = ['is_correct' => 'boolean', 'answered_at' => 'datetime'];

    public function question(): BelongsTo { return $this->belongsTo(LiveQuestion::class, 'live_question_id'); }
    public function option(): BelongsTo { return $this->belongsTo(LiveQuestionOption::class, 'live_question_option_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
