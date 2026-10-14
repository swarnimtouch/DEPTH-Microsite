<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('downloaded_at');
            $table->timestamps();

            $table->index(['module_id', 'user_id', 'downloaded_at'], 'certificate_download_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_download_logs');
    }
};
