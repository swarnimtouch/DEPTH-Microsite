<?php

namespace App\Http\Controllers;

use App\Models\GeneralSettings;
use Illuminate\Http\Request;

class MyController
{
    public function __construct()
    {
        GeneralSettings::define_const();
    }
}
