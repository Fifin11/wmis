<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\User;
use App\Models\RouteAssignment;
use App\Models\CitizenReport;
use App\Models\SystemLog;
use App\Models\RecyclingSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RouteController extends Controller
{
    // Render the Admin Dashboard
    public function dashboard(Request $request)
    {
        $totalRoutes   = Route::count();
        $totalReports  = CitizenReport::count();
        $activeDrivers = User::where('role', 'Driver')->count();
        $pendingRecyclingCount = RecyclingSubmission::where('status', 'Pending')->count();

        // SEARCH/FILTER: Routes
        $routesQuery = Route::query();
        if ($request->filled('route_search')) {
            $routesQuery->where('route_name', 'like', '%' . $request->route_search . '%')
                        ->orWhere('waste_type', 'like', '%' . $request->route_search . '%');
        }
        $routes = $routesQuery->paginate(15, ['*'], 'routes_page');

        // SEARCH/FILTER: Drivers
        $driversQuery = User::where('role', 'Driver');
        if ($request->filled('driver_search')) {
            $driversQuery->where('name', 'like', '%' . $request->driver_search . '%')
                         ->orWhere('email', 'like', '%' . $request->driver_search . '%');
        }
        $drivers = $driversQuery->paginate(15, ['*'], 'drivers_page');

        // SEARCH/FILTER: Reports
        $reportsQuery = CitizenReport::with('citizen');
        if ($request->filled('report_search')) {
            $reportsQuery->where('issue_type', 'like', '%' . $request->report_search . '%')
                         ->orWhere('status', 'like', '%' . $request->report_search . '%');
        }
        if ($request->filled('report_status')) {
            $reportsQuery->where('status', $request->report_status);
        }
        $reports = $reportsQuery->latest()->paginate(15, ['*'], 'reports_page');

        $systemLogs  = SystemLog::with('user')->latest()->paginate(15, ['*'], 'logs_page');
        $assignments = RouteAssignment::with(['route', 'driver'])->latest()->paginate(15, ['*'], 'assignments_page');

        // Recycling moderation queue: pending first, then recent approved/rejected
        $recyclingSubmissions = RecyclingSubmission::with(['citizen', 'reviewer'])
            ->orderByRaw("FIELD(status, 'Pending', 'Approved', 'Rejected')")
            ->latest()
            ->get();

        return view('admin.dashboard', compact(
            'totalRoutes',
            'totalReports',
            'activeDrivers',
            'pendingRecyclingCount',
            'routes',
            'drivers',
            'reports',
            'systemLogs',
            'assignments',
            'recyclingSubmissions',
            'request'
        ));
    }

    // CRUD: Store new route
    public function store(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string|max:255|min:3|unique:routes,route_name',
            'waste_type' => 'required|in:Organic,Plastic,Paper,Hazardous',
            'scheduled_day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'zone_coordinates' => 'required|string', // Expecting JSON string from Leaflet
        ], [
            'route_name.unique' => 'A route with this name already exists. Please choose a different name.',
            'route_name.min' => 'Route name must be at least 3 characters long.',
        ]);

        $coords = json_decode($request->zone_coordinates, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($coords) || empty($coords)) {
            return redirect()->back()->withErrors(['zone_coordinates' => 'Invalid polygon. Please draw at least 3 points on the map.']);
        }

        // Validate polygon has at least 3 points
        if (count($coords) < 3) {
            return redirect()->back()->withErrors(['zone_coordinates' => 'Polygon must have at least 3 vertices. You provided ' . count($coords) . '.']);
        }

        // Validate lat/lng ranges
        foreach ($coords as $index => $point) {
            if (!isset($point['lat'], $point['lng'])) {
                return redirect()->back()->withErrors(['zone_coordinates' => "Coordinate #" . ($index + 1) . " is missing latitude or longitude."]);
            }
            if (!is_numeric($point['lat']) || !is_numeric($point['lng'])) {
                return redirect()->back()->withErrors(['zone_coordinates' => "Coordinate #" . ($index + 1) . " has invalid lat/lng values."]);
            }
            if ($point['lat'] < -90 || $point['lat'] > 90 || $point['lng'] < -180 || $point['lng'] > 180) {
                return redirect()->back()->withErrors(['zone_coordinates' => "Coordinate #" . ($index + 1) . " is outside valid geographic range."]);
            }
        }

        $route = Route::create([
            'route_name' => $request->route_name,
            'waste_type' => $request->waste_type,
            'scheduled_day' => $request->scheduled_day,
            'zone_coordinates' => $coords,
        ]);

        // Audit log action
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create Route',
            'entity_type' => 'Route',
            'entity_id' => $route->id,
            'new_values' => $route->toArray(),
        ]);

        return redirect()->back()->with('success', 'Route created successfully.');
    }

    // CRUD: Destroy route
    public function destroy($id)
    {
        $route = Route::findOrFail($id);
        
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete Route',
            'entity_type' => 'Route',
            'entity_id' => $route->id,
            'old_values' => $route->toArray(),
        ]);

        $route->delete();
        return redirect()->back()->with('success', 'Route deleted successfully.');
    }

    // Assignment: Assign driver to route
    public function assignDriver(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'driver_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
        ]);

        // FR-ADM-01.2: Prevent scheduling conflicting routes for the same driver on the same day.
        $conflict = RouteAssignment::where('driver_id', $request->driver_id)
            ->where('assigned_date', $request->assigned_date)
            ->exists();

        if ($conflict) {
            return redirect()->back()->withErrors(['driver_id' => 'Conflict Detected: Driver is already assigned to another route on this date.']);
        }

        $assignment = RouteAssignment::create([
            'route_id' => $request->route_id,
            'driver_id' => $request->driver_id,
            'assigned_date' => $request->assigned_date,
        ]);

        // Audit log action
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Assign Driver',
            'entity_type' => 'RouteAssignment',
            'entity_id' => $assignment->id,
            'new_values' => $assignment->toArray(),
        ]);

        return redirect()->back()->with('success', 'Driver assigned successfully.');
    }
}
