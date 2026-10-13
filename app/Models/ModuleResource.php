<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleResource extends Model
{
    protected $fillable = [
        'module_id',
        'label',
        'description',
        'category',
        'resource_type',
        'file',
        'external_url',
        'thumbnail',
        'file_type',
        'available_at',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'available_at' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
