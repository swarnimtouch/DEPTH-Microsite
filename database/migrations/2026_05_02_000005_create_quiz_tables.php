<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->text('question');
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('module_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('video_completed_at')->nullable();
            $table->timestamp('quiz_completed_at')->nullable();
            $table->unsignedInteger('quiz_score')->default(0);
            $table->unsignedInteger('quiz_total')->default(0);
            $table->unsignedInteger('progress_percent')->default(0);
            $table->json('answers')->nullable();
            $table->timestamps();
            $table->unique(['module_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_user_progress');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
    }
};
