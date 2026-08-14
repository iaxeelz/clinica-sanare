<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'price',
        'cost',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean'
    ];

    // ============================================
    // RELACIONES EXISTENTES
    // ============================================
    
    /**
     * Relación con citas (relación directa - para compatibilidad)
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // ============================================
    // NUEVAS RELACIONES PARA MÉDICOS (MUCHOS A MUCHOS)
    // ============================================
    
    /**
     * Médicos que ofrecen este servicio (muchos a muchos)
     * A través de la tabla doctor_services
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_services')
                    ->withPivot('is_active', 'extra_charge', 'duration_minutes')
                    ->withTimestamps();
    }

    /**
     * Médicos activos que ofrecen este servicio
     */
    public function activeDoctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_services')
                    ->wherePivot('is_active', true)
                    ->withPivot('extra_charge', 'duration_minutes')
                    ->withTimestamps();
    }

    /**
     * Servicios de la cita a través de appointment_services
     */
    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    // ============================================
    // ACCESORES
    // ============================================
    
    /**
     * Obtener el precio formateado
     */
    public function getFormattedPriceAttribute()
    {
        return 'S/ ' . number_format($this->price, 2);
    }

    /**
     * Obtener el costo formateado
     */
    public function getFormattedCostAttribute()
    {
        return 'S/ ' . number_format($this->cost, 2);
    }

    /**
     * Obtener la ganancia (precio - costo)
     */
    public function getProfitAttribute()
    {
        return $this->price - $this->cost;
    }

    /**
     * Obtener la ganancia formateada
     */
    public function getFormattedProfitAttribute()
    {
        return 'S/ ' . number_format($this->profit, 2);
    }

    /**
     * Obtener el margen de ganancia en porcentaje
     */
    public function getProfitMarginAttribute()
    {
        if ($this->price == 0) {
            return 0;
        }
        return round(($this->profit / $this->price) * 100, 2);
    }

    /**
     * Obtener la duración formateada
     */
    public function getFormattedDurationAttribute()
    {
        if ($this->duration_minutes >= 60) {
            $hours = floor($this->duration_minutes / 60);
            $minutes = $this->duration_minutes % 60;
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'min' : '');
        }
        return $this->duration_minutes . ' min';
    }

    /**
     * Obtener el estado del servicio como texto
     */
    public function getIsActiveTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Obtener el color del estado del servicio
     */
    public function getIsActiveColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Contar cuántos médicos ofrecen este servicio
     */
    public function getDoctorsCountAttribute()
    {
        return $this->doctors()->count();
    }

    /**
     * Contar cuántos médicos activos ofrecen este servicio
     */
    public function getActiveDoctorsCountAttribute()
    {
        return $this->activeDoctors()->count();
    }

    /**
     * Obtener los nombres de los médicos que ofrecen este servicio
     */
    public function getDoctorsListAttribute()
    {
        return $this->doctors->pluck('full_name')->implode(', ');
    }

    /**
     * Obtener los IDs de los médicos que ofrecen este servicio
     */
    public function getDoctorIdsAttribute()
    {
        return $this->doctors->pluck('id')->toArray();
    }

    /**
     * Obtener el total de citas de este servicio
     */
    public function getAppointmentsCountAttribute()
    {
        return $this->appointments()->count();
    }

    /**
     * Obtener el total de citas activas de este servicio
     */
    public function getActiveAppointmentsCountAttribute()
    {
        return $this->appointments()->where('status', '!=', 'cancelada')->count();
    }

    /**
     * Obtener el total de ingresos generados por este servicio
     */
    public function getTotalRevenueAttribute()
    {
        return $this->appointments()
            ->where('is_paid', true)
            ->sum('appointment_services.price');
    }

    // ============================================
    // SCOPES
    // ============================================
    
    /**
     * Scope para búsqueda general
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%")
            ->orWhere('description', 'LIKE', "%{$search}%");
    }

    /**
     * Scope para servicios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para servicios inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para servicios ofrecidos por un médico específico
     */
    public function scopeOfferedByDoctor($query, $doctorId)
    {
        return $query->whereHas('doctors', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        });
    }

    /**
     * Scope para servicios ofrecidos por un médico específico y activos
     */
    public function scopeOfferedByActiveDoctor($query, $doctorId)
    {
        return $query->whereHas('doctors', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId)
              ->wherePivot('is_active', true);
        });
    }

    /**
     * Scope para servicios con precio en un rango
     */
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope para servicios con duración específica
     */
    public function scopeDuration($query, $minutes)
    {
        return $query->where('duration_minutes', $minutes);
    }

    /**
     * Scope para servicios con duración mayor a
     */
    public function scopeDurationGreaterThan($query, $minutes)
    {
        return $query->where('duration_minutes', '>', $minutes);
    }

    /**
     * Scope para servicios con duración menor a
     */
    public function scopeDurationLessThan($query, $minutes)
    {
        return $query->where('duration_minutes', '<', $minutes);
    }

    /**
     * Scope para servicios que tienen al menos un médico asignado
     */
    public function scopeHasDoctors($query)
    {
        return $query->has('doctors');
    }

    /**
     * Scope para servicios que no tienen médicos asignados
     */
    public function scopeWithoutDoctors($query)
    {
        return $query->doesntHave('doctors');
    }

    /**
     * Scope para servicios con ganancia mayor a
     */
    public function scopeProfitGreaterThan($query, $amount)
    {
        return $query->whereRaw('(price - cost) > ?', [$amount]);
    }

    // ============================================
    // MÉTODOS DE UTILIDAD
    // ============================================
    
    /**
     * Verificar si un médico ofrece este servicio
     */
    public function isOfferedByDoctor($doctorId)
    {
        return $this->doctors()->where('doctor_id', $doctorId)->exists();
    }

    /**
     * Verificar si un médico ofrece este servicio y está activo
     */
    public function isOfferedByActiveDoctor($doctorId)
    {
        return $this->doctors()
            ->where('doctor_id', $doctorId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Obtener el precio personalizado para un médico específico
     */
    public function getPriceForDoctor($doctorId)
    {
        $doctorService = $this->doctors()
            ->where('doctor_id', $doctorId)
            ->first();
        
        if ($doctorService && $doctorService->pivot->extra_charge) {
            return $this->price + $doctorService->pivot->extra_charge;
        }
        
        return $this->price;
    }

    /**
     * Obtener la duración personalizada para un médico específico
     */
    public function getDurationForDoctor($doctorId)
    {
        $doctorService = $this->doctors()
            ->where('doctor_id', $doctorId)
            ->first();
        
        if ($doctorService && $doctorService->pivot->duration_minutes) {
            return $doctorService->pivot->duration_minutes;
        }
        
        return $this->duration_minutes;
    }

    /**
     * Activar o desactivar el servicio para un médico específico
     */
    public function toggleForDoctor($doctorId, $active = true)
    {
        return $this->doctors()->updateExistingPivot($doctorId, [
            'is_active' => $active
        ]);
    }

    /**
     * Verificar si el servicio tiene un cargo extra para un médico
     */
    public function hasExtraChargeForDoctor($doctorId)
    {
        $doctorService = $this->doctors()
            ->where('doctor_id', $doctorId)
            ->first();
        
        return $doctorService && $doctorService->pivot->extra_charge !== null && $doctorService->pivot->extra_charge > 0;
    }

    /**
     * Obtener todos los médicos activos que ofrecen este servicio
     */
    public function getAvailableDoctors()
    {
        return $this->activeDoctors()->get();
    }
}