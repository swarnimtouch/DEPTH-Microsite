<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel', 20);
            $table->string('timing', 30);
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['channel', 'timing']);
        });

        $now = now();
        $defaults = [
            ['channel' => 'email', 'timing' => 'one_day_prior', 'name' => 'Email - 1 Day Prior', 'subject' => 'Reminder: {{module_title}} starts tomorrow', 'body' => $this->emailBody('tomorrow')],
            ['channel' => 'email', 'timing' => 'eight_hours_prior', 'name' => 'Email - 8 Hours Prior', 'subject' => 'Reminder: {{module_title}} starts today', 'body' => $this->emailBody('in 8 hours')],
            ['channel' => 'email', 'timing' => 'one_hour_prior', 'name' => 'Email - 1 Hour Prior', 'subject' => 'Reminder: {{module_title}} starts in 1 hour', 'body' => $this->emailBody('in 1 hour')],
            ['channel' => 'whatsapp', 'timing' => 'one_day_prior', 'name' => 'WhatsApp - 1 Day Prior', 'subject' => null, 'body' => $this->shortBody('tomorrow')],
            ['channel' => 'whatsapp', 'timing' => 'eight_hours_prior', 'name' => 'WhatsApp - 8 Hours Prior', 'subject' => null, 'body' => $this->shortBody('in 8 hours')],
            ['channel' => 'whatsapp', 'timing' => 'one_hour_prior', 'name' => 'WhatsApp - 1 Hour Prior', 'subject' => null, 'body' => $this->shortBody('in 1 hour')],
            ['channel' => 'sms', 'timing' => 'one_day_prior', 'name' => 'SMS - 1 Day Prior', 'subject' => null, 'body' => $this->shortBody('tomorrow')],
            ['channel' => 'sms', 'timing' => 'eight_hours_prior', 'name' => 'SMS - 8 Hours Prior', 'subject' => null, 'body' => $this->shortBody('in 8 hours')],
            ['channel' => 'sms', 'timing' => 'one_hour_prior', 'name' => 'SMS - 1 Hour Prior', 'subject' => null, 'body' => $this->shortBody('in 1 hour')],
        ];

        foreach ($defaults as $template) {
            DB::table('reminder_templates')->insert($template + [
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_templates');
    }

    private function emailBody(string $when): string
    {
        return "Dear {{user_name}},<br><br>This is a reminder that <strong>{{module_title}}</strong> starts {$when}.<br><br>Date and time: {{module_start}}<br>Chapter link: {{module_url}}<br><br>Regards,<br>ReproMed Team";
    }

    private function shortBody(string $when): string
    {
        return 'Reminder: {{module_title}} starts ' . $when . '. Join here: {{module_url}}';
    }
};
