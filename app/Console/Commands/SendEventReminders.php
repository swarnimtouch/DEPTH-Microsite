<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\ReminderLog;
use App\Models\ReminderTemplate;
use App\Models\User;
use App\Services\ReminderDeliveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendEventReminders extends Command
{
    protected $signature = 'event-reminders:send {--window=10 : Time window in minutes}';

    protected $description = 'Send email, WhatsApp, and SMS reminders for upcoming chapter events.';

    private array $offsets = [
        'one_day_prior' => 1440,
        'eight_hours_prior' => 480,
        'one_hour_prior' => 60,
    ];

    public function handle(ReminderDeliveryService $delivery): int
    {
        $this->info('Event reminders are disabled. No reminders were sent.');

        return self::SUCCESS;

        $window = max(1, (int) $this->option('window'));
        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($this->offsets as $timing => $minutesBefore) {
            $targetStart = now('UTC')->addMinutes($minutesBefore);
            $from = $targetStart->copy()->subMinutes($window);
            $to = $targetStart->copy()->addMinutes($window);

            $modules = Module::active()
                ->whereNotNull('start_at')
                ->whereBetween('start_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
                ->get();

            if ($modules->isEmpty()) {
                continue;
            }

            $templates = ReminderTemplate::active()
                ->where('timing', $timing)
                ->get();

            if ($templates->isEmpty()) {
                continue;
            }

            $users = User::active()
                ->where('type', 'doctor')
                ->get();

            foreach ($modules as $module) {
                foreach ($templates as $template) {
                    foreach ($users as $user) {
                        $result = $this->sendOnce($delivery, $template, $module, $user);

                        match ($result) {
                            'sent' => $sent++,
                            'failed' => $failed++,
                            default => $skipped++,
                        };
                    }
                }
            }
        }

        $this->info("Reminders processed. Sent: {$sent}, skipped: {$skipped}, failed: {$failed}");

        return self::SUCCESS;
    }

    private function sendOnce(ReminderDeliveryService $delivery, ReminderTemplate $template, Module $module, User $user): string
    {
        $existing = ReminderLog::where([
            'module_id' => $module->id,
            'user_id' => $user->id,
            'channel' => $template->channel,
            'timing' => $template->timing,
        ])->first();

        if ($existing) {
            return 'skipped';
        }

        return DB::transaction(function () use ($delivery, $template, $module, $user) {
            $log = ReminderLog::create([
                'module_id' => $module->id,
                'user_id' => $user->id,
                'reminder_template_id' => $template->id,
                'channel' => $template->channel,
                'timing' => $template->timing,
                'recipient' => $delivery->recipient($template, $user),
                'status' => 'pending',
            ]);

            try {
                $result = $delivery->send($template, $module, $user);
            } catch (Throwable $exception) {
                $result = [
                    'status' => 'failed',
                    'response' => $exception->getMessage(),
                ];
            }

            $log->update([
                'status' => $result['status'],
                'response' => $result['response'] ?? null,
                'sent_at' => in_array($result['status'], ['sent', 'skipped'], true) ? Carbon::now() : null,
            ]);

            return $result['status'];
        });
    }
}
