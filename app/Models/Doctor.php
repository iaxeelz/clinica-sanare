<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty',
        'license_number',
        'consultation_fee',
        'is_active'
    ];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // ============================================
    // RELACIONES EXISTENTES
    // ============================================
    
    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con citas (relación directa - para compatibilidad)
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // ============================================
    // NUEVAS RELACIONES PARA SERVICIOS (MUCHOS A MUCHOS)
    // ============================================
    
    /**
     * Servicios que ofrece este médico (muchos a muchos)
     * A través de la tabla doctor_services
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'doctor_services')
                    ->withPivot('is_active', 'extra_charge', 'duration_minutes')
                    ->withTimestamps();
    }

    /**
     * Solo servicios activos que ofrece este médico
     */
    public function activeServices()
    {
        return $this->belongsToMany(Service::class, 'doctor_services')
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
    // NUEVAS RELACIONES PARA HORARIOS
    // ============================================
    
    /**
     * Horarios de atención del médico
     */
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    /**
     * Horarios activos de atención del médico
     */
    public function activeSchedules()
    {
        return $this->hasMany(DoctorSchedule::class)->where('is_active', true);
    }

    // ============================================
    // RELACIONES PARA HORAS BLOQUEADAS
    // ============================================
    
    /**
     * Horas bloqueadas del médico (no disponibles)
     */
    public function unavailableSlots()
    {
        return $this->hasMany(DoctorUnavailableSlot::class);
    }

    /**
     * Horas bloqueadas del médico para una fecha específica
     */
    public function unavailableSlotsForDate($date)
    {
        return $this->hasMany(DoctorUnavailableSlot::class)->where('date', $date);
    }

    // ============================================
    // RELACIONES PARA DÍAS BLOQUEADOS (NUEVO)
    // ============================================
    
    /**
     * Días bloqueados del médico (no disponibles)
     */
    public function blockedDays()
    {
        return $this->hasMany(DoctorBlockedDay::class);
    }

    /**
     * Días bloqueados del médico para una fecha específica
     */
    public function blockedDaysForDate($date)
    {
        return $this->hasMany(DoctorBlockedDay::class)->where('date', $date);
    }

    /**
     * Verificar si un día está bloqueado
     */
    public function isDayBlocked($date)
    {
        return $this->blockedDays()->where('date', $date)->where('is_active', true)->exists();
    }

    // ============================================
    // ACCESORES
    // ============================================
    
    /**
     * Obtener el nombre completo del médico (del usuario asociado)
     */
    public function getFullNameAttribute()
    {
        return $this->user ? $this->user->name : 'Sin nombre';
    }

    /**
     * Obtener el email del médico (del usuario asociado)
     */
    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : 'Sin email';
    }

    /**
     * Obtener el teléfono del médico (del usuario asociado)
     */
    public function getPhoneAttribute()
    {
        return $this->user ? $this->user->phone : null;
    }

    /**
     * Verificar si el médico está activo
     */
    public function getIsActiveTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Color para el estado del médico
     */
    public function getIsActiveColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Contar cuántos servicios ofrece este médico
     */
    public function getServicesCountAttribute()
    {
        return $this->services()->count();
    }

    /**
     * Obtener los nombres de los servicios que ofrece como texto
     */
    public function getServicesListAttribute()
    {
        return $this->services->pluck('name')->implode(', ');
    }

    /**
     * Obtener las especialidades de los servicios que ofrece (para filtros)
     */
    public function getServiceNamesAttribute()
    {
        return $this->services->pluck('name')->toArray();
    }

    // ============================================
    // SCOPES
    // ============================================
    
    /**
     * Scope para búsqueda general
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%");
        })->orWhere('specialty', 'LIKE', "%{$search}%")
          ->orWhere('license_number', 'LIKE', "%{$search}%");
    }

    /**
     * Scope para médicos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para médicos inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para médicos que pueden ofrecer un servicio específico
     */
    public function scopeCanProvide($query, $serviceId)
    {
        return $query->whereHas('services', function($q) use ($serviceId) {
            $q->where('service_id', $serviceId)
              ->where('is_active', true);
        });
    }

    /**
     * Scope para médicos por especialidad (búsqueda parcial)
     */
    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', 'LIKE', "%{$specialty}%");
    }

    /**
     * Scope para médicos con licencia específica
     */
    public function scopeByLicense($query, $licenseNumber)
    {
        return $query->where('license_number', $licenseNumber);
    }

    /**
     * Scope para médicos que tienen al menos un servicio asignado
     */
    public function scopeHasServices($query)
    {
        return $query->has('services');
    }

    /**
     * Scope para médicos que tienen citas en una fecha específica
     */
    public function scopeWithAppointmentsOnDate($query, $date)
    {
        return $query->whereHas('appointments', function($q) use ($date) {
            $q->whereDate('appointment_date', $date)
              ->where('status', '!=', 'cancelada');
        });
    }

    // ============================================
    // MÉTODOS DE UTILIDAD
    // ============================================
    
    /**
     * Verificar si el médico ofrece un servicio específico
     */
    public function offersService($serviceId)
    {
        return $this->services()->where('service_id', $serviceId)->exists();
    }

    /**
     * Verificar si el médico ofrece un servicio específico y está activo
     */
    public function offersActiveService($serviceId)
    {
        return $this->services()
            ->where('service_id', $serviceId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Obtener la duración personalizada para un servicio (si existe)
     */
    public function getServiceDuration($serviceId)
    {
        $service = $this->services()
            ->where('service_id', $serviceId)
            ->first();
        
        if ($service && $service->pivot->duration_minutes) {
            return $service->pivot->duration_minutes;
        }
        
        // Si no tiene duración personalizada, usar la del servicio
        $service = Service::find($serviceId);
        return $service ? $service->duration_minutes : 30;
    }

    /**
     * Obtener el precio personalizado para un servicio (si existe)
     */
    public function getServicePrice($serviceId)
    {
        $service = $this->services()
            ->where('service_id', $serviceId)
            ->first();
        
        if ($service && $service->pivot->extra_charge) {
            $basePrice = Service::find($serviceId)?->price ?? 0;
            return $basePrice + $service->pivot->extra_charge;
        }
        
        return Service::find($serviceId)?->price ?? 0;
    }

    /**
     * Obtener todos los IDs de servicios que ofrece
     */
    public function getServiceIdsAttribute()
    {
        return $this->services->pluck('id')->toArray();
    }

    /**
     * Verificar si el médico tiene citas en una fecha específica
     */
    public function hasAppointmentsOnDate($date)
    {
        return $this->appointments()
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelada')
            ->exists();
    }

    /**
     * Obtener las citas de un médico en una fecha específica
     */
    public function getAppointmentsOnDate($date)
    {
        return $this->appointments()
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelada')
            ->get();
    }
}