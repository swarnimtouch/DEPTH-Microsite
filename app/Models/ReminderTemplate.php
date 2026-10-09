<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderTemplate extends Model
{
    public const CHANNELS = ['email', 'whatsapp', 'sms'];

    public const TIMINGS = [
        'one_day_prior' => '1 day prior to event',
        'eight_hours_prior' => 'On event day, 8 hours prior',
        'one_hour_prior' => '1 hour prior to event',
    ];

    protected $fillable = [
        'name',
        'channel',
        'timing',
        'subject',
        'body',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
