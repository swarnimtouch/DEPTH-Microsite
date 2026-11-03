<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyAvailabilityChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $moduleId, public string $surveyType, public bool $enabled) {}

    public function broadcastOn(): Channel
    {
        return new Channel('surveys.module.'.$this->moduleId);
    }

    public function broadcastAs(): string
    {
        return 'survey.availability-changed';
    }

    public function broadcastWith(): array
    {
        return ['survey_type' => $this->surveyType, 'enabled' => $this->enabled];
    }
}
