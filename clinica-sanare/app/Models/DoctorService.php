<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorService extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'doctor_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',
        'service_id',
        'is_active',
        'extra_charge',
        'duration_minutes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'extra_charge' => 'decimal:2',
        'duration_minutes' => 'integer'
    ];

    // ============================================
    // RELACIONES
    // ============================================
    
    /**
     * Relación con el médico
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Relación con el servicio
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // ============================================
    // ACCESORES
    // ============================================
    
    /**
     * Obtener el nombre del médico
     */
    public function getDoctorNameAttribute()
    {
        return $this->doctor?->full_name ?? 'Médico no encontrado';
    }

    /**
     * Obtener el nombre del servicio
     */
    public function getServiceNameAttribute()
    {
        return $this->service?->name ?? 'Servicio no encontrado';
    }

    /**
     * Obtener el precio total (precio base + cargo extra)
     */
    public function getTotalPriceAttribute()
    {
        $basePrice = $this->service?->price ?? 0;
        return $basePrice + ($this->extra_charge ?? 0);
    }

    /**
     * Obtener el precio formateado
     */
    public function getFormattedTotalPriceAttribute()
    {
        return 'S/ ' . number_format($this->total_price, 2);
    }

    /**
     * Obtener la duración (personalizada o la del servicio)
     */
    public function getEffectiveDurationAttribute()
    {
        return $this->duration_minutes ?? $this->service?->duration_minutes ?? 30;
    }

    /**
     * Obtener la duración formateada
     */
    public function getFormattedDurationAttribute()
    {
        $duration = $this->effective_duration;
        if ($duration >= 60) {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'min' : '');
        }
        return $duration . ' min';
    }

    /**
     * Obtener el estado como texto
     */
    public function getIsActiveTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Obtener el color del estado
     */
    public function getIsActiveColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Verificar si tiene cargo extra
     */
    public function getHasExtraChargeAttribute()
    {
        return $this->extra_charge !== null && $this->extra_charge > 0;
    }

    /**
     * Obtener el cargo extra formateado
     */
    public function getFormattedExtraChargeAttribute()
    {
        if ($this->has_extra_charge) {
            return 'S/ ' . number_format($this->extra_charge, 2);
        }
        return 'Sin cargo extra';
    }

    // ============================================
    // SCOPES
    // ============================================
    
    /**
     * Scope para registros activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para registros inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para un médico específico
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope para un servicio específico
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope para registros con cargo extra
     */
    public function scopeWithExtraCharge($query)
    {
        return $query->whereNotNull('extra_charge')->where('extra_charge', '>', 0);
    }

    /**
     * Scope para registros sin cargo extra
     */
    public function scopeWithoutExtraCharge($query)
    {
        return $query->whereNull('extra_charge')->orWhere('extra_charge', 0);
    }

    /**
     * Scope para registros con duración personalizada
     */
    public function scopeWithCustomDuration($query)
    {
        return $query->whereNotNull('duration_minutes');
    }

    // ============================================
    // MÉTODOS DE UTILIDAD
    // ============================================
    
    /**
     * Activar o desactivar el servicio para el médico
     */
    public function toggleActive()
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Activar el servicio para el médico
     */
    public function activate()
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Desactivar el servicio para el médico
     */
    public function deactivate()
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Actualizar el cargo extra
     */
    public function updateExtraCharge($amount)
    {
        $this->extra_charge = $amount;
        return $this->save();
    }

    /**
     * Actualizar la duración personalizada
     */
    public function updateDuration($minutes)
    {
        $this->duration_minutes = $minutes;
        return $this->save();
    }

    /**
     * Verificar si el médico puede ofrecer este servicio
     */
    public function isAvailable()
    {
        return $this->is_active && $this->doctor?->is_active;
    }

    /**
     * Obtener información completa para mostrar
     */
    public function getDisplayInfoAttribute()
    {
        return [
            'doctor_id' => $this->doctor_id,
            'doctor_name' => $this->doctor_name,
            'service_id' => $this->service_id,
            'service_name' => $this->service_name,
            'price' => $this->total_price,
            'formatted_price' => $this->formatted_total_price,
            'duration' => $this->effective_duration,
            'formatted_duration' => $this->formatted_duration,
            'is_active' => $this->is_active,
            'status_text' => $this->is_active_text,
            'status_color' => $this->is_active_color,
            'has_extra_charge' => $this->has_extra_charge,
            'extra_charge' => $this->extra_charge,
            'formatted_extra_charge' => $this->formatted_extra_charge
        ];
    }
}