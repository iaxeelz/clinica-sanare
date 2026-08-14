<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorBlockedDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'reason',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getDateFormattedAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    public function getReasonShortAttribute()
    {
        return $this->reason ? \Illuminate\Support\Str::limit($this->reason, 30) : 'Sin motivo';
    }

    public function getIsActiveTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    public function getIsActiveColorAttribute()
    {
        return $this->is_active ? 'danger' : 'secondary';
    }
}