<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comments extends Model
{
    protected $fillable = [
        'episode_id',
        'user_id',
        'comment_id',
        'comment',
        'status',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'episode_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'comment_id');
    }

    public function upvotes(): HasMany
    {
        return $this->hasMany(CommentUpvote::class, 'comment_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'publish');
    }
}
