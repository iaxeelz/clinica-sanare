<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorUnavailableSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'reason',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getStartTimeFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->start_time)->format('h:i A');
    }

    public function getEndTimeFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->end_time)->format('h:i A');
    }

    // ============================================
    // SCOPES
    // ============================================
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // ============================================
    // MÉTODOS DE UTILIDAD
    // ============================================
    
    public function getDurationMinutesAttribute()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        return $start->diffInMinutes($end);
    }

    public function getDurationFormattedAttribute()
    {
        $minutes = $this->duration_minutes;
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return $hours . 'h ' . ($mins > 0 ? $mins . 'min' : '');
        }
        return $minutes . ' min';
    }

    public function getIsActiveTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    public function getIsActiveColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }
}