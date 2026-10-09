<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ReminderTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReminderDeliveryService
{
    public function render(string $content, Module $module, User $user): string
    {
        $start = $module->start_at?->copy()->timezone(config('app.timezone'))->format('d M Y, h:i A') ?: 'To be announced';

        $tokens = [
            '{{user_name}}' => $user->name ?: 'Delegate',
            '{{user_email}}' => $user->email ?: '',
            '{{user_mobile}}' => $user->mobile ?: '',
            '{{module_title}}' => $module->title,
            '{{module_start}}' => $start,
            '{{module_url}}' => route('modules.show', $module->slug),
        ];

        return strtr($content, $tokens);
    }

    public function recipient(ReminderTemplate $template, User $user): ?string
    {
        return $template->channel === 'email' ? $user->email : $user->mobile;
    }

    public function send(ReminderTemplate $template, Module $module, User $user): array
    {
        $recipient = $this->recipient($template, $user);

        if (!filled($recipient)) {
            return ['status' => 'skipped', 'response' => 'Recipient not available'];
        }

        $body = $this->render($template->body, $module, $user);

        if ($template->channel === 'email') {
            $subject = $this->render($template->subject ?: $template->name, $module, $user);
            Mail::html($body, fn($message) => $message->to($recipient)->subject($subject));

            return ['status' => 'sent', 'response' => 'Email sent'];
        }

        return $this->sendHttpMessage($template->channel, $recipient, trim(strip_tags($body)));
    }

    private function sendHttpMessage(string $channel, string $recipient, string $message): array
    {
        $url = config("services.{$channel}.url");
        $token = config("services.{$channel}.token");

        if (!filled($url)) {
            Log::info("{$channel} reminder skipped because provider URL is not configured", [
                'recipient' => $recipient,
                'message' => Str::limit($message, 120),
            ]);

            return ['status' => 'skipped', 'response' => strtoupper($channel) . ' provider URL not configured'];
        }

        $request = Http::acceptJson();

        if (filled($token)) {
            $request = $request->withToken($token);
        }

        $response = $request->post($url, [
            'to' => $recipient,
            'message' => $message,
            'channel' => $channel,
        ]);

        return [
            'status' => $response->successful() ? 'sent' : 'failed',
            'response' => Str::limit($response->body(), 1000, ''),
        ];
    }
}
