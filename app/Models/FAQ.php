<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FAQ extends Model
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
