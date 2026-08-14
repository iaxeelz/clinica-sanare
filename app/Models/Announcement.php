<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'type',
        'is_active',
        'start_date',
        'end_date',
        'user_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'campaign' => 'warning',
            'festivity' => 'success',
            'info' => 'info',
            'warning' => 'danger',
            default => 'secondary'
        };
    }

    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'campaign' => 'fa-bullhorn',
            'festivity' => 'fa-gift',
            'info' => 'fa-info-circle',
            'warning' => 'fa-exclamation-triangle',
            default => 'fa-circle'
        };
    }
}