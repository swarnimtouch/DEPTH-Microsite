<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_view_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 80);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('total_seconds')->default(0);
            $table->unsignedInteger('during_event_seconds')->default(0);
            $table->unsignedInteger('post_event_seconds')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'user_id', 'session_id']);
            $table->index(['module_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_view_sessions');
    }
};
