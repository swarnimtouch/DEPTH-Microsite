<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleResource extends Model
{
    protected $fillable = [
        'module_id',
        'label',
        'file',
        'file_type',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
