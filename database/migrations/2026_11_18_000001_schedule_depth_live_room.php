<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules')
            ->where('slug', 'recognising-managing-depression-primary-care')
            ->update([
                'start_date' => '2026-11-20',
                'end_date' => '2026-11-20',
                'start_at' => '2026-11-20 10:30:00',
                'end_at' => '2026-11-20 12:00:00',
                'duration' => '1:30',
                'join_enabled' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Keep the corrected event schedule on rollback.
    }
};
