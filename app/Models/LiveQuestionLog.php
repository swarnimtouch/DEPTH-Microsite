<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveQuestionLog extends Model
{
    protected $fillable = ['live_question_id', 'user_id', 'admin_id', 'action', 'meta'];
    protected $casts = ['meta' => 'array'];
    public function question() { return $this->belongsTo(LiveQuestion::class, 'live_question_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
}
