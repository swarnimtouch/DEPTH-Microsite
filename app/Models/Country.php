<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    //
    protected $table = 'countries';

    protected $fillable = ['name', 'iso2', 'phonecode'];

    public function mobileLengthRange(): array
    {
        return match (strtoupper($this->iso2)) {
            'IN' => [10, 10],
            'IS' => [7, 7],
            'KR' => [9, 10],
            'MM' => [7, 10],
            default => [6, 15],
        };
    }

    public function displayName(): string
    {
        return match ($this->name) {
            'Korea South' => 'South Korea',
            'Korea North' => 'North Korea',
            default => $this->name,
        };
    }
}
