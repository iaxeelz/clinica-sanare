<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'dni',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'emergency_contact',
        'allergies',
        'medical_history',
        'is_active'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relación con citas (se implementará en el módulo de citas)
     public function appointments()
     {
         return $this->hasMany(Appointment::class);
    }

    // Accesor para nombre completo
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where('first_name', 'LIKE', "%{$search}%")
                     ->orWhere('last_name', 'LIKE', "%{$search}%")
                     ->orWhere('dni', 'LIKE', "%{$search}%")
                     ->orWhere('phone', 'LIKE', "%{$search}%");
    }
}