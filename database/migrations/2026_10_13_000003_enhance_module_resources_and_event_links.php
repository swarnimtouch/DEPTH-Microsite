<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('join_url', 2048)->nullable()->after('video_url');
        });

        Schema::table('module_resources', function (Blueprint $table) {
            $table->text('description')->nullable()->after('label');
            $table->string('resource_type', 20)->default('auto')->after('category');
            $table->string('thumbnail')->nullable()->after('external_url');
            $table->dateTime('available_at')->nullable()->after('file_type');
            $table->string('status', 20)->default('active')->after('available_at');
        });
    }

    public function down(): void
    {
        Schema::table('module_resources', function (Blueprint $table) {
            $table->dropColumn(['description', 'resource_type', 'thumbnail', 'available_at', 'status']);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('join_url');
        });
    }
};
