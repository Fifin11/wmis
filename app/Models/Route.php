<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = ['route_name', 'waste_type', 'scheduled_day', 'zone_coordinates'];

    protected $casts = [
        'zone_coordinates' => 'array', // Decodes the JSON coordinate layout arrays safely for Leaflet.js
    ];

    public function pickupLogs()
    {
        return $this->hasMany(PickupLog::class);
    }

    public function drivers()
    {
        return $this->belongsToMany(User::class, 'route_assignments', 'route_id', 'driver_id')
                    ->withPivot('assigned_date')
                    ->withTimestamps();
    }
}