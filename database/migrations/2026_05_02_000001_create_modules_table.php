<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('speaker_name')->nullable();
            $table->string('speaker_designation')->nullable();
            $table->string('speaker_hospital')->nullable();
            $table->string('duration')->nullable();
            $table->decimal('cme_credit', 4, 1)->default(1);
            $table->string('thumbnail')->nullable();
            $table->string('video_url')->nullable();
            $table->longText('overview')->nullable();
            $table->longText('key_takeaways')->nullable();
            $table->longText('resources')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
