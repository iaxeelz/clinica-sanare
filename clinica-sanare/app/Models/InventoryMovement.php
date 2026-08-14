<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'price',
        'description',
        'user_id'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    // Relación con inventario
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accesor para tipo con color
    public function getTypeColorAttribute()
    {
        return $this->type === 'entrada' ? 'success' : 'danger';
    }

    // Accesor para tipo con texto
    public function getTypeTextAttribute()
    {
        return $this->type === 'entrada' ? 'Entrada' : 'Salida';
    }
}