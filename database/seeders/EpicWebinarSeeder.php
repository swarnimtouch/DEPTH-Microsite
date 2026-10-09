<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EpicWebinarSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $course = Course::active()->first() ?? Course::query()->first() ?? new Course();

            if (! $course->exists) {
                $course->title = 'Decoding Epilepsy: From Diagnosis to Quality of Life';
            }

            $course->fill([
                'subtitle' => 'Close diagnostic gaps. Optimize treatment. Move beyond seizure freedom.',
                'about' => 'Two expert-led webinars exploring contemporary approaches to epilepsy diagnosis, treatment optimization and personalized patient care.',
                'status' => 'active',
            ])->save();


            $webinars = [
                [
                    'title' => 'Epilepsy & Seizure Management - Closing the Diagnosis & Treatment Gap',
                    'venue' => 'Online webinar',
                    'slug' => 'epic-webinar-1-diagnosis-treatment-gap',
                    'speaker_name' => 'EAN speaker to be confirmed',
                    'speaker_designation' => 'European Academy of Neurology faculty',
                    'duration' => '1 hour',
                    'overview' => 'Strengthen diagnostic accuracy and make informed, timely treatment decisions from the first seizure to the first prescription.',
                    'start_at' => '2026-10-28 10:30:00',
                    'end_at' => '2026-10-28 11:30:00',
                    'start_date' => '2026-10-28',
                    'end_date' => '2026-10-28',
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Optimizing Epilepsy Management - From Seizure Control to Quality of Life',
                    'venue' => 'Online webinar',
                    'slug' => 'epic-webinar-2-quality-of-life',
                    'speaker_name' => 'Prof. Ley Sander',
                    'speaker_designation' => 'Professor of Neurology, UCL Queen Square Institute of Neurology',
                    'duration' => '1 hour',
                    'overview' => 'Move beyond seizure control to tailor epilepsy therapy around individual patient needs, treatment burden and quality of life.',
                    'start_at' => '2026-12-04 10:30:00',
                    'end_at' => '2026-12-04 11:30:00',
                    'start_date' => '2026-12-04',
                    'end_date' => '2026-12-04',
                    'sort_order' => 2,
                ],
            ];

            Module::where('course_id', $course->id)
                ->whereIn('slug', [
                    'chapter-1-manila-gdmt-gap',
                    'chapter-2-bangkok-challenging-patients',
                    'chapter-3-jakarta-patient-profiling',
                ])
                ->update(['status' => 'inactive']);

            foreach ($webinars as $webinar) {
                Module::updateOrCreate(
                    ['slug' => $webinar['slug']],
                    $webinar + [
                        'course_id' => $course->id,
                        'cme_credit' => 0,
                        'key_takeaways' => null,
                        'video_url' => null,
                        'is_locked' => false,
                        'status' => 'active',
                    ]
                );
            }
        });
    }
}

