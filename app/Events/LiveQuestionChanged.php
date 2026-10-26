<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveQuestionChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $moduleId, public string $action, public ?int $questionId = null) {}
    public function broadcastOn(): Channel { return new Channel('live-questions.module.' . $this->moduleId); }
    public function broadcastAs(): string { return 'live-question.changed'; }
    public function broadcastWith(): array { return ['action' => $this->action, 'question_id' => $this->questionId]; }
}
