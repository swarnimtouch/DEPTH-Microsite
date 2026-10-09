<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PulceConnectChapterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $course = Course::active()->first() ?? Course::query()->first() ?? new Course();
            $course->fill([
                'title' => 'PULCE Connect 2026',
                'subtitle' => 'Cardiovascular & Heart Failure Education Programme',
                'about' => 'A three-chapter hybrid education programme across Manila, Bangkok and Jakarta, connecting heart failure expertise across Asia.',
                'status' => 'active',
            ])->save();

            Course::where('id', '!=', $course->id)->update(['status' => 'inactive']);

            $chapters = [
                [
                    'title' => 'Closing the GDMT Gap: From Evidence to Early Implementation',
                    'venue' => 'Manila, Philippines',
                    'slug' => 'chapter-1-manila-gdmt-gap',
                    'start_at' => '2026-10-28 11:00:00',
                    'end_at' => '2026-10-28 13:00:00',
                    'start_date' => '2026-10-28',
                    'end_date' => '2026-10-28',
                    'sort_order' => 1,
                    'time_zone' => 'Asia/Manila',
                ],
                [
                    'title' => 'Managing Challenging Heart Failure Patients',
                    'venue' => 'Bangkok, Thailand',
                    'slug' => 'chapter-2-bangkok-challenging-patients',
                    'start_at' => '2026-10-30 08:00:00',
                    'end_at' => '2026-10-30 10:00:00',
                    'start_date' => '2026-10-30',
                    'end_date' => '2026-10-30',
                    'sort_order' => 2,
                    'time_zone' => 'Asia/Bangkok',
                ],
                [
                    'title' => 'Personalizing Cardiovascular Care Through Patient Profiling',
                    'venue' => 'Jakarta, Indonesia',
                    'slug' => 'chapter-3-jakarta-patient-profiling',
                    'start_at' => '2026-10-31 08:30:00',
                    'end_at' => '2026-10-31 10:30:00',
                    'start_date' => '2026-10-31',
                    'end_date' => '2026-10-31',
                    'sort_order' => 3,
                    'time_zone' => 'Asia/Jakarta',
                ],
            ];

            $desiredSlugs = collect($chapters)->pluck('slug');
            // Keep historical chapter records and their participant activity, but
            // archive them so only the new programme chapters appear on the site.
            Module::whereNotIn('slug', $desiredSlugs)->update(['status' => 'inactive']);

            foreach ($chapters as $chapter) {
                unset($chapter['time_zone']);
                $values = $chapter + [
                    'course_id' => $course->id,
                    'duration' => '2 hours',
                    'cme_credit' => 0,
                    'overview' => null,
                    'key_takeaways' => null,
                    'video_url' => null,
                    'thumbnail' => null,
                    'is_locked' => false,
                    'status' => 'active',
                ];
                Module::updateOrCreate(['slug' => $chapter['slug']], $values);
            }
        });
    }
}
