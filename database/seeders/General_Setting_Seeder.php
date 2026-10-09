<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB; // ✅ IMPORTANT

use Illuminate\Database\Seeder;

class General_Setting_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $keys = collect([
            'label',
            'unique_name',
            'type',
            'value',
            'options',
            'extra',
            'hint',
        ]);
        $values = [
            [
                'Site Name',
                'site_name',
                'text',
                'name',
                null,
                json_encode([
                    'required' => 'required',
                ]),
                'Please Enter Site name',
            ],
            [
                'Site Logo',
                'site_logo',
                'file',
                '',
                null,
                json_encode([
                    'accept' => "image/*",
                ]),
                'Site Logo Main'
            ],
            [
                'Small Site Logo',
                'small_site_logo',
                'file',
                '',
                null,
                json_encode([
                    'accept' => "image/*",
                ]),
                'Site Small Logo Main'
            ],
            [
                'Fav Icon',
                'Favicon',
                'file',
                '',
                null,
                json_encode([
                    'accept' => "image/*",
                ]),
                'Fav Icon for Site'
            ],
            [
                'Footer Text',
                'footer_text',
                'textarea',
                'Footer Text',
                null,
                json_encode([
                    'maxlength' => "255",
                    'required' => 'required',
                ]),
                'Please Enter Site footer text'
            ],
            [
                'Admin Email',
                'ADMIN_EMAIL',
                'email',
                'admin@gmail.com',
                null,
                json_encode([
                    'maxlength' => "255",
                    'required' => 'required',
                ]),
                'Please Enter Email Address For Admin'
            ],
            [
                'Admin Phone',
                'ADMIN_phone',
                'number',
                '1234567890',
                null,
                json_encode([
                    'maxlength' => "20",
                    'required' => 'required',
                ]),
                'Please Enter Phone Number For Admin'
            ],
        ];
        foreach ($values as $key => $value) {
            $data = $keys->combine($value);
            DB::table('general_settings')->insert($data->all());
        }

    }
}
