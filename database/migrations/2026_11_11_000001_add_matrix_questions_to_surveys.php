<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->string('question_type')->default('single_choice')->after('question');
        });

        Schema::create('survey_question_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->string('row_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('survey_answers', function (Blueprint $table) {
            $table->foreignId('survey_question_row_id')->nullable()->after('survey_question_id')
                ->constrained('survey_question_rows')->nullOnDelete();
            $table->string('row_text')->nullable()->after('question_text');
        });
    }

    public function down(): void
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('survey_question_row_id');
            $table->dropColumn('row_text');
        });

        Schema::dropIfExists('survey_question_rows');

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropColumn('question_type');
        });
    }
};
