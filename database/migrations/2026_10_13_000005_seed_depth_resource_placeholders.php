<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $moduleId = DB::table('modules')
            ->where('slug', 'recognising-managing-depression-primary-care')
            ->value('id');

        if (!$moduleId) {
            return;
        }

        $resources = [
            ['Full Webinar Recording', 'Complete recording of the DEPTH live session.', 'full_recording', 'video'],
            ['1-Page Post-read / Synopsis', 'Concise post-session synopsis and key discussion points.', 'post_read', 'pdf'],
            ['Snippet videos', 'Short video highlights from the live session.', 'snippet_video', 'video'],
            ['Key clinical pearls', 'Short, memorable clinical messages from the session.', 'key_pearls', 'pdf'],
            ['Selected evidence / references', 'Studies and guidelines discussed during the session.', 'references', 'pdf'],
            ['Practical takeaways', 'Key learning points designed for everyday clinical practice.', 'practical_takeaways', 'pdf'],
            ['The patient hiding in plain sight', 'Recognizing depression masked by somatic complaints in your daily OPD.', 'case_book', 'pdf'],
            ['Diagnosing with confidence', 'Using PHQ-9 as a primary diagnosis and monitoring tool.', 'case_book', 'pdf'],
            ["The diabetic who won't comply", 'Depression in diabetes and cardiovascular disease.', 'case_book', 'pdf'],
            ['Choosing the right antidepressant for your patient', 'A practical pharmacotherapy guide for matching treatment to the patient.', 'case_book', 'pdf'],
        ];

        foreach ($resources as $sortOrder => [$label, $description, $category, $type]) {
            $exists = DB::table('module_resources')
                ->where('module_id', $moduleId)
                ->where('category', $category)
                ->where('label', $label)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('module_resources')->insert([
                'module_id' => $moduleId,
                'label' => $label,
                'description' => $description,
                'category' => $category,
                'resource_type' => $type,
                'file' => null,
                'external_url' => null,
                'thumbnail' => null,
                'file_type' => null,
                'available_at' => null,
                'status' => 'active',
                'sort_order' => $sortOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $moduleId = DB::table('modules')
            ->where('slug', 'recognising-managing-depression-primary-care')
            ->value('id');

        if (!$moduleId) {
            return;
        }

        DB::table('module_resources')
            ->where('module_id', $moduleId)
            ->whereNull('file')
            ->whereNull('external_url')
            ->whereIn('category', [
                'full_recording',
                'post_read',
                'snippet_video',
                'key_pearls',
                'references',
                'practical_takeaways',
                'case_book',
            ])
            ->delete();
    }
};
