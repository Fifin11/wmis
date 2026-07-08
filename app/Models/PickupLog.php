<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupLog extends Model
{
    protected $fillable = ['route_id', 'driver_id', 'status', 'start_time', 'end_time', 'start_lat', 'start_lng', 'end_lat', 'end_lng', 'cleared_nodes'];

    protected $casts = [
        'cleared_nodes' => 'array',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}