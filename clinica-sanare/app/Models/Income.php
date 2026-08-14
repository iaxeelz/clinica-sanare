<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'service_id',
        'doctor_id',
        'cost_price',
        'sale_price',
        'amount_paid',
        'change_amount',
        'doctor_payment',
        'payment_method',
        'receipt_number',
        'invoice_number',
        'description',
        'payment_date',
        'user_id',
        'is_active'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'doctor_payment' => 'decimal:2',
        'payment_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accesor para método de pago con color
    public function getPaymentMethodColorAttribute()
    {
        return match($this->payment_method) {
            'efectivo' => 'success',
            'yape' => 'info',
            'tarjeta_culqi' => 'primary',
            default => 'secondary'
        };
    }

    // Accesor para método de pago con texto
    public function getPaymentMethodTextAttribute()
    {
        return match($this->payment_method) {
            'efectivo' => 'Efectivo',
            'yape' => 'Yape',
            'tarjeta_culqi' => 'Tarjeta Culqi',
            default => 'Desconocido'
        };
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('patient', function ($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
              ->orWhere('dni', 'LIKE', "%{$search}%");
        })->orWhereHas('service', function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%");
        })->orWhere('receipt_number', 'LIKE', "%{$search}%")
          ->orWhere('invoice_number', 'LIKE', "%{$search}%");
    }

    // Scope para filtrar por fecha
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('payment_date', $date);
    }

    // Scope para filtrar por método de pago
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}