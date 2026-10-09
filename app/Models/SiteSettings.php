<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    //
    protected $table = 'general_settings';
    protected $fillable = [
        'label',
        'unique_name',
        'type',
        'value',
        'options',
        'class',
        'extra',
        'hint',
        'status',
        'order_number'
    ];

    protected $casts = [
        'order_number' => 'integer',
    ];
}
