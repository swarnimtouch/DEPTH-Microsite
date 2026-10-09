<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            [
                'title'   => 'About Us',
                'slug'    => Str::slug('About Us'),
                'content' => 'This is the About Us page content.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'   => 'Privacy Policy',
                'slug'    => Str::slug('Privacy Policy'),
                'content' => 'This is the Privacy Policy content.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'   => 'Terms & Conditions',
                'slug'    => Str::slug('Terms & Conditions'),
                'content' => 'This is the Terms & Conditions content.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('contents')->insert($contents);
    }
}
