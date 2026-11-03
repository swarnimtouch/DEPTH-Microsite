<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('pre_survey_enabled')->default(false)->after('join_enabled');
            $table->boolean('post_survey_enabled')->default(false)->after('pre_survey_enabled');
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->enum('survey_type', ['pre', 'post']);
            $table->text('question');
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['module_id', 'survey_type', 'status']);
        });

        Schema::create('survey_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->string('option_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('survey_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('survey_type', ['pre', 'post']);
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['module_id', 'user_id', 'survey_type']);
        });

        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('survey_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question_text');
            $table->text('answer_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_submissions');
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('survey_questions');
        Schema::table('modules', fn (Blueprint $table) => $table->dropColumn(['pre_survey_enabled', 'post_survey_enabled']));
    }
};
