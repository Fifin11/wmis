<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class SecondaryDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get a citizen for reports
        $citizen = User::where('role', 'Citizen')->first();
        if (!$citizen) {
            $this->command->warn("No citizen found to assign reports.");
            return;
        }

        // 2. Add Incident Reports
        DB::table('citizen_reports')->insert([
            [
                'citizen_id' => $citizen->id,
                'issue_type' => 'Illegal Dumping',
                'description' => 'Large pile of construction debris dumped illegally near the riverbank on River Road.',
                'location_lat' => 6.680,
                'location_lng' => 80.400,
                'status' => 'Open',
                'created_at' => Carbon::now()->subHours(5),
                'updated_at' => Carbon::now()->subHours(5)
            ],
            [
                'citizen_id' => $citizen->id,
                'issue_type' => 'Hazardous Waste',
                'description' => 'Medical waste found near the central park, posing a severe health hazard.',
                'location_lat' => 6.685,
                'location_lng' => 80.405,
                'status' => 'Investigating',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subHours(2)
            ],
            [
                'citizen_id' => $citizen->id,
                'issue_type' => 'Missed Pickup',
                'description' => 'Garbage truck did not collect waste from our lane this Monday.',
                'location_lat' => 6.690,
                'location_lng' => 80.395,
                'status' => 'Resolved',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(1)
            ]
        ]);

        // 3. Get Drivers and Routes
        $drivers = User::where('role', 'Driver')->take(2)->get();
        $route = DB::table('routes')->first();

        if ($drivers->count() > 0 && $route) {
            foreach ($drivers as $index => $driver) {
                // Assign each driver to the route on different days
                DB::table('route_assignments')->insert([
                    'route_id' => $route->id,
                    'driver_id' => $driver->id,
                    'assigned_date' => Carbon::tomorrow()->addDays($index)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
