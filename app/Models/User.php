<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
    protected $fillable = [
        'id',
        'type',
        'status',
        'first_name',
        'last_name',
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'mobile',
        'country',
        'hospital',
        'role',
        'speciality_id',
        'license_no',
        'profile_image',
        'remember_token',
        'last_login_at',
        'created_at',
        'updated_at',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The attributes that should have default values.
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'doctor',
    ];


    public function getProfileImageAttribute($value): string
    {
        if (empty($value)) {
            return asset('assets/media/avatars/blank.png');
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return asset($value);
        }

        return asset("storage/" . $value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class);
    }

}
