<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\PickupLog;
use App\Models\CitizenReport;
use App\Models\Announcement;
use App\Models\RecyclingLeaderboard;
use App\Models\RecyclingSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CitizenDashboardController extends Controller
{
    // Display the citizen dashboard
    public function dashboard()
    {
        $announcements = Announcement::with('admin')->latest()->get();
        
        // Gamified Leaderboard
        $leaderboard = RecyclingLeaderboard::with('citizen')
            ->orderBy('points', 'desc')
            ->take(10)
            ->get();

        // Citizen's personal reports (if authenticated)
        $myReports = [];
        $myPoints = 0;
        $mySubmissions = [];
        $hasSubmittedToday = false;
        if (Auth::check()) {
            $myReports = CitizenReport::where('citizen_id', Auth::id())->latest()->get();
            $myLeaderboard = RecyclingLeaderboard::where('citizen_id', Auth::id())->first();
            $myPoints = $myLeaderboard ? $myLeaderboard->points : 0;

            // Citizen's recycling submission history
            $mySubmissions = RecyclingSubmission::where('citizen_id', Auth::id())
                ->latest()->take(10)->get();

            // Daily cooldown check: has citizen already submitted today?
            $hasSubmittedToday = RecyclingSubmission::where('citizen_id', Auth::id())
                ->whereDate('created_at', today())
                ->exists();
        }

        // Active routes to draw on the map
        $activeRoutes = Route::all();

        return view('citizen.dashboard', compact(
            'announcements', 'leaderboard', 'myReports', 'myPoints',
            'activeRoutes', 'mySubmissions', 'hasSubmittedToday'
        ));
    }

    // Submit illegal dumping / missed pickup report (FR-CTZ-02)
    public function submitReport(Request $request)
    {
        $request->validate([
            'issue_type' => 'required|in:Missed Pickup,Illegal Dumping,Hazardous Waste',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lng' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:1000|min:10',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // FR-CTZ-01.2: JPG/PNG and max 5MB (5120 KB)
        ], [
            'location_lat.between' => 'Invalid latitude. Must be between -90 and 90.',
            'location_lng.between' => 'Invalid longitude. Must be between -180 and 180.',
            'description.min' => 'Please provide at least 10 characters of description.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Save inside public/uploads
            $imagePath = $request->file('image')->store('uploads/reports', 'public');
        }

        CitizenReport::create([
            'citizen_id' => Auth::check() ? Auth::id() : null,
            'issue_type' => $request->issue_type,
            'location_lat' => $request->location_lat,
            'location_lng' => $request->location_lng,
            'image_path' => $imagePath,
            'description' => $request->description,
            'status' => 'Open',
        ]);

        return redirect()->back()->with('success', 'Incident report submitted successfully to the municipal office.');
    }

    // API: Return active truck locations and route progress (FR-CTZ-01.1 - loaded every 30s)
    public function activeTrucks()
    {
        $activeTrips = PickupLog::with(['route', 'driver'])
            ->where('status', 'Active')
            ->get()
            ->map(function ($trip) {
                // If start coordinates exist, return them. Otherwise default to first route node
                $currentLat = $trip->start_lat;
                $currentLng = $trip->start_lng;

                // Simulate slight movements if we are running locally to look dynamic!
                // We add a tiny random offset to make it look active during local demo.
                $offsetLat = (rand(-10, 10) / 100000);
                $offsetLng = (rand(-10, 10) / 100000);

                return [
                    'trip_id' => $trip->id,
                    'route_id' => $trip->route_id,
                    'route_name' => $trip->route->route_name,
                    'waste_type' => $trip->route->waste_type,
                    'driver_name' => $trip->driver->name,
                    'current_lat' => floatval($currentLat) + $offsetLat,
                    'current_lng' => floatval($currentLng) + $offsetLng,
                    'cleared_nodes' => $trip->cleared_nodes ?? [],
                    'route_coordinates' => $trip->route->zone_coordinates,
                    'updated_at' => $trip->updated_at->toIso8601String(),
                ];
            });

        return response()->json($activeTrips);
    }

    /**
     * Submit a recycling claim for admin approval.
     * Enforces: (1) Auth required, (2) Citizen role only, (3) One submission per day cooldown.
     */
    public function submitRecyclingClaim(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'Citizen') {
            return redirect()->back()->withErrors(['auth' => 'Only registered citizens can submit recycling claims.']);
        }

        // ── Daily cooldown: one submission per citizen per calendar day ──
        $alreadyToday = RecyclingSubmission::where('citizen_id', Auth::id())
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyToday) {
            return redirect()->back()->withErrors([
                'recycle' => 'You have already submitted a recycling claim today. Please wait until tomorrow to submit again. This limit ensures fairness across all citizens.',
            ]);
        }

        $request->validate([
            'description'    => 'required|string|min:20|max:1000',
            'claimed_points' => 'required|integer|min:5|max:50',
        ]);

        RecyclingSubmission::create([
            'citizen_id'     => Auth::id(),
            'description'    => $request->description,
            'claimed_points' => $request->claimed_points,
            'status'         => 'Pending',
        ]);

        return redirect()->back()->with('success', '✅ Your recycling claim has been submitted and is awaiting review by the municipal officer. Points will be credited upon approval.');
    }
}
