<?php

namespace Database\Seeders;

use App\Models\Speciality;
use Illuminate\Database\Seeder;

class SpecialitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Cardiology',
            'Heart failure',
            'Internal medicine',
            'Interventional cardiology',
            'Other',
        ] as $name) {
            Speciality::firstOrCreate(
                ['name' => $name],
                ['status' => 'active']
            );
        }
    }
}
