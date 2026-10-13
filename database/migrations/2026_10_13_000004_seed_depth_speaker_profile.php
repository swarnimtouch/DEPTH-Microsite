<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $module = DB::table('modules')->where('slug', 'recognising-managing-depression-primary-care')->first();
        if (!$module) {
            return;
        }

        DB::table('modules')->where('id', $module->id)->update([
            'speaker_name' => 'Prof. Siegfried Kasper',
            'speaker_designation' => 'Professor Emeritus of Psychiatry',
            'speaker_hospital' => 'Medical University of Vienna',
            'speakers' => json_encode([[
                'name' => 'Prof. Siegfried Kasper',
                'role' => 'Speaker',
                'current_position' => 'Professor Emeritus of Psychiatry, Medical University of Vienna',
                'designation' => 'Professor Emeritus of Psychiatry',
                'hospital' => 'Medical University of Vienna',
                'country' => 'Austria',
                'city' => 'Vienna',
                'bio' => 'Professor Emeritus of Psychiatry and former Head of the Department of Psychiatry and Psychotherapy at the Medical University of Vienna, Austria. A specialist in psychiatry and neurology, his research focuses on depression, neuropsychopharmacology, neurobiology and translational psychiatry. He has authored more than 800 PubMed-indexed publications and has held leadership roles in several international psychiatric organisations, including serving as President of the World Federation of Societies of Biological Psychiatry (WFSBP).',
                'expertise' => 'Depression & Mood Disorders, Neuropsychopharmacology, Biological & Translational Psychiatry, Psychopharmacology, Translational Psychiatry',
                'session_title' => 'Recognising & Managing Depression in Primary Care',
                'profile_resource_url' => '',
                'image' => 'web/depth/assets/speakers/kasper.jpg',
            ]], JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Speaker content is intentionally retained on rollback.
    }
};
