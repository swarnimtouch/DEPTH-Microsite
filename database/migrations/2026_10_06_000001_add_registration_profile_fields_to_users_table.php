<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 100)->nullable()->after('hospital');
            $table->foreignId('speciality_id')->nullable()->after('role')->constrained('specialities')->nullOnDelete();
            $table->string('license_no', 100)->nullable()->after('speciality_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('speciality_id');
            $table->dropColumn(['role', 'license_no']);
        });
    }
};
