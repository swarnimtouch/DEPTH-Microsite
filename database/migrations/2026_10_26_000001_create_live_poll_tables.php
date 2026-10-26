<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['poll', 'quiz']);
            $table->text('question');
            $table->enum('status', ['draft', 'live', 'stopped'])->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
        });

        Schema::create('live_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_question_id')->constrained('live_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('live_question_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_question_id')->constrained('live_questions')->cascadeOnDelete();
            $table->foreignId('live_question_option_id')->constrained('live_question_options')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at');
            $table->timestamps();
            $table->unique(['live_question_id', 'user_id']);
        });

        Schema::create('live_question_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_question_id')->nullable()->constrained('live_questions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_question_logs');
        Schema::dropIfExists('live_question_responses');
        Schema::dropIfExists('live_question_options');
        Schema::dropIfExists('live_questions');
    }
};
