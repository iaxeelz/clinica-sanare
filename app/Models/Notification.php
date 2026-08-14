<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'link',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope para no leídas
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope para leídas
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    // Marcar como leída
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    // Marcar como no leída
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    // Obtener color según tipo
    public function getColorAttribute($value)
    {
        return $value ?? match($this->type) {
            'new_appointment' => 'success',
            'appointment_today' => 'info',
            'appointment_reminder' => 'warning',
            'low_stock' => 'danger',
            'announcement' => 'primary',
            default => 'secondary'
        };
    }

    // Obtener icono según tipo
    public function getIconAttribute($value)
    {
        return $value ?? match($this->type) {
            'new_appointment' => 'fa-calendar-plus',
            'appointment_today' => 'fa-calendar-check',
            'appointment_reminder' => 'fa-clock',
            'low_stock' => 'fa-exclamation-triangle',
            'announcement' => 'fa-bullhorn',
            default => 'fa-bell'
        };
    }

    // Tiempo transcurrido desde que se creó
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}