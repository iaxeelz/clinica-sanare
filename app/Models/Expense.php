<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'concept',
        'amount',
        'category',
        'expense_date',
        'receipt_number',
        'description',
        'user_id',
        'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accesor para categoría con color
    public function getCategoryColorAttribute()
    {
        return match($this->category) {
            'compra_inventario' => 'info',
            'servicios' => 'warning',
            'sueldos' => 'primary',
            'otros' => 'secondary',
            default => 'secondary'
        };
    }

    // Accesor para categoría con texto
    public function getCategoryTextAttribute()
    {
        return match($this->category) {
            'compra_inventario' => 'Compra de Inventario',
            'servicios' => 'Servicios',
            'sueldos' => 'Sueldos',
            'otros' => 'Otros',
            default => 'Desconocido'
        };
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where('concept', 'LIKE', "%{$search}%")
                     ->orWhere('receipt_number', 'LIKE', "%{$search}%")
                     ->orWhere('description', 'LIKE', "%{$search}%");
    }

    // Scope para filtrar por fecha
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('expense_date', $date);
    }

    // Scope para filtrar por categoría
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}