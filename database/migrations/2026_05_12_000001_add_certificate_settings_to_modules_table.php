<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('certificate_template')->nullable()->after('thumbnail');
            $table->decimal('certificate_name_x', 8, 2)->nullable()->after('certificate_template');
            $table->decimal('certificate_name_y', 8, 2)->nullable()->after('certificate_name_x');
            $table->unsignedSmallInteger('certificate_font_size')->nullable()->after('certificate_name_y');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_template',
                'certificate_name_x',
                'certificate_name_y',
                'certificate_font_size',
            ]);
        });
    }
};
