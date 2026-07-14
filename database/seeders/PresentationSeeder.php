<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RecyclingLeaderboard;
use Carbon\Carbon;

class PresentationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get existing Admin
        $admin = User::where('role', 'Admin')->first();
        if (!$admin) {
            $this->command->warn("No admin found. Please create an admin first.");
            return;
        }

        // 2. Create 10 Citizens for Leaderboard
        $citizenIds = [];
        $names = [
            'Saman Kumara', 'Nimali Perera', 'Kasun Silva', 'Amila Roshan', 
            'Piyal Gunawardana', 'Champa Weerasinghe', 'Ruwan Fernando', 
            'Sanduni Maheshika', 'Dilan Tharuka', 'Kavindi Nimesha'
        ];
        
        foreach ($names as $index => $name) {
            $email = 'citizen' . ($index + 1) . '@demo.lk';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('citizen123'),
                    'role' => 'Citizen',
                    'language' => 'en'
                ]
            );
            $citizenIds[] = $user->id;
        }

        // 3. Populate Leaderboard for current month
        $currentMonth = date('F');
        $currentYear = intval(date('Y'));
        
        $points = [2150, 1980, 1750, 1420, 1300, 1100, 950, 800, 620, 450];
        
        foreach ($citizenIds as $index => $cid) {
            DB::table('recycling_leaderboard')->updateOrInsert(
                ['citizen_id' => $cid, 'month' => $currentMonth, 'year' => $currentYear],
                ['points' => $points[$index], 'rank' => $index + 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 4. Add Announcements
        DB::table('announcements')->insertOrIgnore([
            [
                'admin_id' => $admin->id,
                'title_en' => 'Special E-Waste Collection Drive',
                'title_si' => 'විශේෂ ඉලෙක්ට්‍රොනික අපද්‍රව්‍ය එකතු කිරීමේ වැඩසටහන',
                'content_en' => 'This weekend we will be collecting e-waste at the town hall. Bring your old electronics for extra eco-points!',
                'content_si' => 'මෙම සති අන්තයේ නගර ශාලාවේදී ඉලෙක්ට්‍රොනික අපද්‍රව්‍ය එකතු කරනු ලැබේ. අමතර ලකුණු ලබා ගන්න!',
                'published_at' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'admin_id' => $admin->id,
                'title_en' => 'Holiday Schedule Change',
                'title_si' => 'නිවාඩු දින කාලසටහන් වෙනස',
                'content_en' => 'Please note that garbage collection for Zone Alpha will be shifted from Monday to Tuesday next week.',
                'content_si' => 'කරුණාකර සලකන්න, ඊළඟ සතියේ සඳුදා වෙනුවට අඟහරුවාදා කසළ එකතු කරනු ලැබේ.',
                'published_at' => Carbon::now()->subHours(5),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 5. Add a Route
        $routeId = DB::table('routes')->insertGetId([
            'route_name' => 'Zone Alpha - Town Center',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => json_encode([
                ["lat" => 6.682, "lng" => 80.399],
                ["lat" => 6.685, "lng" => 80.402],
                ["lat" => 6.681, "lng" => 80.405]
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 6. Add a Pending Recycling Submission for the first citizen
        DB::table('recycling_submissions')->insertOrIgnore([
            'citizen_id' => $citizenIds[0],
            'description' => 'Collected 5kg of PET plastic bottles from the neighborhood.',
            'claimed_points' => 50,
            'status' => 'Pending',
            'created_at' => Carbon::now()->subHours(2),
            'updated_at' => Carbon::now()->subHours(2)
        ]);
        
        DB::table('recycling_submissions')->insertOrIgnore([
            'citizen_id' => $citizenIds[1],
            'description' => 'Recycled old CRT television.',
            'claimed_points' => 100,
            'status' => 'Approved',
            'admin_note' => 'Approved. Verified by e-waste center.',
            'reviewed_by' => $admin->id,
            'reviewed_at' => Carbon::now()->subDays(1),
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(1)
        ]);
    }
}
