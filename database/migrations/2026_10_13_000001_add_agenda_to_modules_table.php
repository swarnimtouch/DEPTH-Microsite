<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->json('agenda')->nullable()->after('speakers');
        });

        DB::table('modules')->select(['id', 'speakers'])->orderBy('id')->each(function ($module) {
            $rows = json_decode($module->speakers ?: '[]', true);
            if (is_array($rows) && collect($rows)->contains(fn ($row) => is_array($row) && array_key_exists('type', $row))) {
                DB::table('modules')->where('id', $module->id)->update([
                    'agenda' => json_encode($rows),
                    'speakers' => null,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('modules')->select(['id', 'speakers', 'agenda'])->orderBy('id')->each(function ($module) {
            if (!$module->speakers && $module->agenda) {
                DB::table('modules')->where('id', $module->id)->update(['speakers' => $module->agenda]);
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('agenda');
        });
    }
};
