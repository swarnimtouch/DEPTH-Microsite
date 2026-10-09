<?php

namespace App\Events;

use App\Models\Comments;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleCommentCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Comments $comment)
    {
        $this->comment->loadMissing('user');
    }

    public function broadcastOn(): Channel
    {
        return new Channel('comments.module.' . $this->comment->episode_id);
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'parent_id' => $this->comment->comment_id,
                'name' => $this->comment->user?->name ?: 'Delegate',
                'initial' => mb_substr($this->comment->user?->name ?: 'D', 0, 1),
                'comment' => $this->comment->comment,
                'created_at' => $this->comment->created_at?->diffForHumans() ?: 'just now',
                'upvotes_count' => 0,
                'upvoted_by_user' => false,
                'replies' => [],
            ],
        ];
    }
}
