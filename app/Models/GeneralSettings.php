<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSettings extends Model
{
    public static function define_const()
    {
        $all_settings = self::where('status', 'active')->get(['unique_name', 'value', 'type']);
        foreach ($all_settings as $key => $value) {
            if (!defined($value['unique_name'])) {
                define($value['unique_name'], GeneralSettings::CheckType($value['type'], $value['value']));
            }
        }
    }

    public static function CheckType($type = "", $val = "")
    {
        return ($type == "file") ? asset('storage/site_settings/' . $val) : $val;
    }
}
