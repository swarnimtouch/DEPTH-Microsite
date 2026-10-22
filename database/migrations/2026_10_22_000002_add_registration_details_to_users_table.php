<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile_country_code', 5)->nullable()->after('mobile');
            $table->string('mobile_country_iso', 2)->nullable()->after('mobile_country_code');
            $table->foreignId('registered_module_id')->nullable()->after('speciality_id')->constrained('modules')->nullOnDelete();
            $table->timestamp('registration_consent_at')->nullable()->after('registered_module_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registered_module_id');
            $table->dropColumn(['mobile_country_code', 'mobile_country_iso', 'registration_consent_at']);
        });
    }
};
