<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_resources', function (Blueprint $table) {
            $table->string('category', 40)->default('general')->after('label');
            $table->string('external_url', 2048)->nullable()->after('file');
            $table->unsignedInteger('sort_order')->default(0)->after('file_type');
            $table->string('file')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('module_resources', function (Blueprint $table) {
            $table->dropColumn(['category', 'external_url', 'sort_order']);
            $table->string('file')->nullable(false)->change();
        });
    }
};
