<?php

use Database\Seeders\DepthWebinarQuestionnaireSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(DepthWebinarQuestionnaireSeeder::class)->run();
    }

    public function down(): void
    {
        // Keep questionnaire and response history intact during a rollback.
    }
};
