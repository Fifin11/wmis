<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\PickupLog;
use App\Models\RouteAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverPortalController extends Controller
{
    // Display the driver portal
    public function portal()
    {
        $driverId = Auth::id();

        // Get routes assigned to this driver
        $assignments = RouteAssignment::with('route')
            ->where('driver_id', $driverId)
            ->get();

        // Find any active trip for the driver
        $activeTrip = PickupLog::with('route')
            ->where('driver_id', $driverId)
            ->where('status', 'Active')
            ->first();

        // Get past trips completed by this driver
        $tripHistory = PickupLog::with('route')
            ->where('driver_id', $driverId)
            ->where('status', 'Completed')
            ->latest()
            ->take(10)
            ->get();

        return view('driver.dashboard', compact('assignments', 'activeTrip', 'tripHistory'));
    }

    // Start a new trip
    public function startTrip(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'start_lat' => 'required|numeric|between:-90,90',
            'start_lng' => 'required|numeric|between:-180,180',
        ], [
            'start_lat.between' => 'Invalid latitude. Must be between -90 and 90.',
            'start_lng.between' => 'Invalid longitude. Must be between -180 and 180.',
        ]);

        $driverId = Auth::id();

        // Ensure no other active trip is running for this driver
        $existing = PickupLog::where('driver_id', $driverId)
            ->where('status', 'Active')
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors(['error' => 'You already have an active route run in progress. Complete or cancel the current trip first.']);
        }

        PickupLog::create([
            'route_id' => $request->route_id,
            'driver_id' => $driverId,
            'status' => 'Active',
            'start_time' => now(),
            'start_lat' => $request->start_lat,
            'start_lng' => $request->start_lng,
            'cleared_nodes' => [],
        ]);

        return redirect()->back()->with('success', 'Trip started successfully. Your location is being tracked.');
    }

    // Toggle cleared state for a polygon checkpoint node
    public function toggleNode(Request $request)
    {
        $request->validate([
            'pickup_log_id' => 'required|exists:pickup_logs,id',
            'node_index' => 'required|integer',
        ]);

        $log = PickupLog::findOrFail($request->pickup_log_id);
        
        // Ensure driver owns this log and it is active
        if ($log->driver_id !== Auth::id() || $log->status !== 'Active') {
            return response()->json(['error' => 'Unauthorized action or inactive trip.'], 403);
        }

        $cleared = $log->cleared_nodes ?? [];
        $nodeIndex = (int) $request->node_index;

        if (in_array($nodeIndex, $cleared)) {
            // Remove node if already cleared (toggle off)
            $cleared = array_diff($cleared, [$nodeIndex]);
        } else {
            // Add node to cleared list (toggle on)
            $cleared[] = $nodeIndex;
        }

        // Re-index array values for clean JSON encoding
        $log->cleared_nodes = array_values($cleared);
        $log->save();

        return response()->json([
            'success' => true,
            'cleared_nodes' => $log->cleared_nodes
        ]);
    }

    // Complete the active trip
    public function completeTrip(Request $request)
    {
        $request->validate([
            'pickup_log_id' => 'required|exists:pickup_logs,id',
            'end_lat' => 'required|numeric|between:-90,90',
            'end_lng' => 'required|numeric|between:-180,180',
        ], [
            'end_lat.between' => 'Invalid latitude. Must be between -90 and 90.',
            'end_lng.between' => 'Invalid longitude. Must be between -180 and 180.',
        ]);

        $log = PickupLog::findOrFail($request->pickup_log_id);

        if ($log->driver_id !== Auth::id() || $log->status !== 'Active') {
            return redirect()->back()->withErrors(['error' => 'Invalid trip completion request. Ensure you own this active trip.']);
        }

        $log->update([
            'status' => 'Completed',
            'end_time' => now(),
            'end_lat' => $request->end_lat,
            'end_lng' => $request->end_lng,
        ]);

        return redirect()->back()->with('success', 'Trip completed and zone logged successfully.');
    }
}
