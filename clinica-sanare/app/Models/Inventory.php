<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'name',
        'category',
        'description',
        'quantity',
        'min_stock',
        'unit',
        'purchase_price',
        'sale_price',
        'expiration_date',
        'supplier',
        'location',
        'barcode',
        'is_active'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'expiration_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relación con movimientos
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Verificar si está bajo de stock
    public function getIsLowStockAttribute()
    {
        return $this->quantity <= $this->min_stock;
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%")
                     ->orWhere('category', 'LIKE', "%{$search}%")
                     ->orWhere('supplier', 'LIKE', "%{$search}%")
                     ->orWhere('barcode', 'LIKE', "%{$search}%");
    }

    // Scope para filtrar por categoría
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Scope para artículos activos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}