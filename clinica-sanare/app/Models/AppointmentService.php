<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AppointmentService extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appointment_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appointment_id',
        'service_id',
        'doctor_id',
        'price',
        'duration_minutes',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer'
    ];

    // ============================================
    // RELACIONES
    // ============================================
    
    /**
     * Relación con la cita principal
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Relación con el servicio
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relación con el médico
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
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
     * Obtener el nombre del servicio
     */
    public function getServiceNameAttribute()
    {
        return $this->service?->name ?? 'Servicio no encontrado';
    }

    /**
     * Obtener el nombre del médico
     */
    public function getDoctorNameAttribute()
    {
        return $this->doctor?->full_name ?? 'Médico no encontrado';
    }

    /**
     * Obtener la hora de inicio de este servicio dentro de la cita
     */
    public function getStartTimeAttribute()
    {
        if (!$this->appointment) {
            return null;
        }
        
        $offset = $this->appointment->appointmentServices()
            ->where('id', '<', $this->id)
            ->sum('duration_minutes');
        
        return Carbon::parse($this->appointment->appointment_time)->addMinutes($offset);
    }

    /**
     * Obtener la hora de fin de este servicio dentro de la cita
     */
    public function getEndTimeAttribute()
    {
        if (!$this->appointment) {
            return null;
        }
        
        $start = $this->start_time;
        if (!$start) {
            return null;
        }
        
        return $start->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Obtener el estado del servicio (basado en el estado de la cita)
     */
    public function getStatusAttribute()
    {
        return $this->appointment?->status ?? 'pendiente';
    }

    /**
     * Obtener el color del estado
     */
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

    /**
     * Obtener el texto del estado
     */
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

    /**
     * Verificar si el servicio está pagado (basado en la cita)
     */
    public function getIsPaidAttribute()
    {
        return $this->appointment?->is_paid ?? false;
    }

    /**
     * Obtener el color del pago
     */
    public function getPaymentStatusColorAttribute()
    {
        return $this->is_paid ? 'success' : 'danger';
    }

    /**
     * Obtener el texto del pago
     */
    public function getPaymentStatusTextAttribute()
    {
        return $this->is_paid ? 'Pagado' : 'No Pagado';
    }

    // ============================================
    // SCOPES
    // ============================================
    
    /**
     * Scope para servicios por médico
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope para servicios por servicio
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope para servicios por cita
     */
    public function scopeForAppointment($query, $appointmentId)
    {
        return $query->where('appointment_id', $appointmentId);
    }

    /**
     * Scope para servicios activos (cita no cancelada)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('appointment', function($q) {
            $q->where('status', '!=', 'cancelada');
        });
    }

    /**
     * Scope para servicios pagados
     */
    public function scopePaid($query)
    {
        return $query->whereHas('appointment', function($q) {
            $q->where('is_paid', true);
        });
    }

    /**
     * Scope para servicios no pagados
     */
    public function scopeUnpaid($query)
    {
        return $query->whereHas('appointment', function($q) {
            $q->where('is_paid', false);
        });
    }

    /**
     * Scope para servicios por rango de fechas
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereHas('appointment', function($q) use ($startDate, $endDate) {
            $q->whereBetween('appointment_date', [$startDate, $endDate]);
        });
    }

    /**
     * Scope para servicios por fecha específica
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereHas('appointment', function($q) use ($date) {
            $q->whereDate('appointment_date', $date);
        });
    }

    // ============================================
    // MÉTODOS DE UTILIDAD
    // ============================================
    
    /**
     * Verificar si el servicio tiene notas
     */
    public function getHasNotesAttribute()
    {
        return !empty($this->notes);
    }

    /**
     * Calcular el costo del servicio (precio - ganancia del médico)
     * Nota: Esto es un estimado, el costo real puede variar
     */
    public function getEstimatedCostAttribute()
    {
        // Si el médico tiene una tarifa de consulta, usarla
        if ($this->doctor && $this->doctor->consultation_fee > 0) {
            return $this->price - $this->doctor->consultation_fee;
        }
        
        // Si no, estimar un 30% del precio como ganancia del médico
        return $this->price * 0.7;
    }

    /**
     * Calcular la ganancia estimada del médico
     */
    public function getEstimatedDoctorProfitAttribute()
    {
        if ($this->doctor && $this->doctor->consultation_fee > 0) {
            return $this->doctor->consultation_fee;
        }
        
        return $this->price * 0.3;
    }

    /**
     * Obtener el nombre completo del servicio para mostrar en calendario
     */
    public function getCalendarTitleAttribute()
    {
        $patientName = $this->appointment?->patient?->full_name ?? 'Paciente';
        $serviceName = $this->service_name;
        
        return $patientName . ' - ' . $serviceName;
    }

    /**
     * Obtener el color para el calendario basado en el servicio
     */
    public function getCalendarColorAttribute()
    {
        // Puedes personalizar colores por servicio o usar el color de la cita
        return match($this->service?->name) {
            'Pediatría' => '#3498db',
            'Obstetricia' => '#2ecc71',
            'Cardiología' => '#e74c3c',
            'Dermatología' => '#f39c12',
            'Neurología' => '#9b59b6',
            default => '#1a5276'
        };
    }

    /**
     * Duplicar este servicio para otra cita (útil para citas recurrentes)
     */
    public function duplicateForAppointment($appointmentId)
    {
        return self::create([
            'appointment_id' => $appointmentId,
            'service_id' => $this->service_id,
            'doctor_id' => $this->doctor_id,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes
        ]);
    }
}