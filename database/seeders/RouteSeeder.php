<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        // Route 1: Main Town Hall Commercial Zone
        Route::create([
            'route_name' => 'Ratnapura Town Center - Zone A',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [
                ['lat' => 6.6825, 'lng' => 80.3995],
                ['lat' => 6.6850, 'lng' => 80.4030],
                ['lat' => 6.6800, 'lng' => 80.4050],
                ['lat' => 6.6780, 'lng' => 80.4000],
            ],
        ]);

        // Route 2: New Town Residential Area
        Route::create([
            'route_name' => 'New Town Residential - Zone B',
            'waste_type' => 'Plastic',
            'scheduled_day' => 'Wednesday',
            'zone_coordinates' => [
                ['lat' => 6.6910, 'lng' => 80.4120],
                ['lat' => 6.6950, 'lng' => 80.4180],
                ['lat' => 6.6890, 'lng' => 80.4200],
                ['lat' => 6.6860, 'lng' => 80.4130],
            ],
        ]);
    }
}