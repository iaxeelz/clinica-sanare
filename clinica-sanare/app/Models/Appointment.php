<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'reason',
        'is_active',
        'is_paid',
        'paid_at',
        'payment_method',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'payment_method' => 'string',
    ];

    // ============================================
    // RELACIONES EXISTENTES
    // ============================================
    
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================
    // NUEVAS RELACIONES PARA MÚLTIPLES SERVICIOS
    // ============================================
    
    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function services()
    {
        return $this->hasManyThrough(
            Service::class,
            AppointmentService::class,
            'appointment_id',
            'id',
            'id',
            'service_id'
        );
    }

    public function doctors()
    {
        return $this->hasManyThrough(
            Doctor::class,
            AppointmentService::class,
            'appointment_id',
            'id',
            'id',
            'doctor_id'
        );
    }

    // ============================================
    // ACCESORES PARA COMPATIBILIDAD
    // ============================================
    
    public function getFirstServiceAttribute()
    {
        return $this->appointmentServices->first()?->service;
    }

    public function getFirstDoctorAttribute()
    {
        return $this->appointmentServices->first()?->doctor;
    }

    public function getServicesListAttribute()
    {
        return $this->appointmentServices->pluck('service.name')->implode(', ');
    }

    public function getDoctorsListAttribute()
    {
        return $this->appointmentServices->pluck('doctor.full_name')->implode(', ');
    }

    public function getTotalPriceAttribute()
    {
        return $this->appointmentServices->sum('price');
    }

    public function getTotalDurationAttribute()
    {
        return (int) $this->appointmentServices->sum('duration_minutes');
    }

    public function getHasMultipleServicesAttribute()
    {
        return $this->appointmentServices->count() > 1;
    }

    public function getServicesCountAttribute()
    {
        return $this->appointmentServices->count();
    }

    // ============================================
    // MÉTODOS PARA CÁLCULO DE HORARIOS - CORREGIDOS
    // ============================================
    
    public function getServiceOffset($appointmentServiceId)
    {
        return (int) $this->appointmentServices()
            ->where('id', '<', $appointmentServiceId)
            ->sum('duration_minutes');
    }

    public function getServiceStartTime($appointmentServiceId)
    {
        $offset = (int) $this->getServiceOffset($appointmentServiceId);
        return Carbon::parse($this->appointment_time)->addMinutes($offset);
    }

    public function getServiceEndTime($appointmentServiceId)
    {
        $appointmentService = $this->appointmentServices()->find($appointmentServiceId);
        $start = $this->getServiceStartTime($appointmentServiceId);
        $duration = (int) $appointmentService->duration_minutes;
        return $start->copy()->addMinutes($duration);
    }

    public function getEndTimeAttribute()
    {
        $start = Carbon::parse($this->appointment_time);
        return $start->addMinutes($this->total_duration);
    }

    // ============================================
    // ACCESORES EXISTENTES (STATUS)
    // ============================================
    
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pendiente' => 'warning',
            'confirmada' => 'info',
            'en_curso' => 'primary',
            'completada' => 'success',
            'cancelada' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'en_curso' => 'En Curso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            default => 'Desconocido'
        };
    }

    // ============================================
    // ACCESORES PARA PAGO
    // ============================================
    
    public function getPaymentStatusColorAttribute()
    {
        return $this->is_paid ? 'success' : 'danger';
    }

    public function getPaymentStatusTextAttribute()
    {
        return $this->is_paid ? 'Pagado' : 'No Pagado';
    }

    public function getPaymentMethodColorAttribute()
    {
        return match($this->payment_method) {
            'efectivo' => 'success',
            'yape' => 'info',
            'tarjeta_culqi' => 'primary',
            default => 'secondary'
        };
    }

    public function getPaymentMethodTextAttribute()
    {
        return match($this->payment_method) {
            'efectivo' => 'Efectivo',
            'yape' => 'Yape',
            'tarjeta_culqi' => 'Tarjeta Culqi',
            default => 'No especificado'
        };
    }

    // ============================================
    // SCOPES
    // ============================================
    
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('patient', function ($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
              ->orWhere('dni', 'LIKE', "%{$search}%");
        })->orWhereHas('doctor', function ($q) use ($search) {
            $q->whereHas('user', function ($u) use ($search) {
                $u->where('name', 'LIKE', "%{$search}%");
            });
        })->orWhere('appointment_date', 'LIKE', "%{$search}%");
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->whereHas('appointmentServices', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelada');
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeWithMultipleServices($query)
    {
        return $query->has('appointmentServices', '>', 1);
    }

    public function scopeWithService($query, $serviceId)
    {
        return $query->whereHas('appointmentServices', function($q) use ($serviceId) {
            $q->where('service_id', $serviceId);
        });
    }
}