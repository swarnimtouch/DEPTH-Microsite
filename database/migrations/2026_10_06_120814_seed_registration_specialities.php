<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        foreach (['Cardiology', 'Heart failure', 'Internal medicine', 'Interventional cardiology', 'Other'] as $name) {
            if (!DB::table('specialities')->where('name', $name)->exists()) {
                DB::table('specialities')->insert([
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep seeded specialities because they may now be used by registered doctors.
    }
};
