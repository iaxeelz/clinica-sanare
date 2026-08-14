<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    // Relación con médico
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // Accesor para día de la semana en texto
    public function getDayNameAttribute()
    {
        $days = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        return $days[$this->day_of_week] ?? 'Desconocido';
    }

    // Accesor para día abreviado
    public function getDayShortAttribute()
    {
        $days = [
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mié',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sáb',
            7 => 'Dom'
        ];
        return $days[$this->day_of_week] ?? 'Des';
    }

    // Accesor para hora de inicio formateada
    public function getStartTimeFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->start_time)->format('h:i A');
    }

    // Accesor para hora de fin formateada
    public function getEndTimeFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->end_time)->format('h:i A');
    }
}