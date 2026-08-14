<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;  // <-- AGREGA ESTA LÍNEA

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;  // <-- AGREGA HasRoles AQUÍ

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}