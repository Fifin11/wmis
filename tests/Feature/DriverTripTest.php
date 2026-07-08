<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Route;
use App\Models\RouteAssignment;
use App\Models\PickupLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTripTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test driver can start a trip
     */
    public function test_driver_can_start_trip()
    {
        $driver = User::factory()->create(['role' => 'Driver']);
        
        $route = Route::create([
            'route_name' => 'Test Route',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        $response = $this->actingAs($driver)
            ->post('/driver/trip/start', [
                'route_id' => $route->id,
                'start_lat' => 6.927079,
                'start_lng' => 80.771179,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('pickup_logs', [
            'driver_id' => $driver->id,
            'route_id' => $route->id,
            'status' => 'Active',
        ]);
    }

    /**
     * Test driver cannot start multiple trips simultaneously
     */
    public function test_driver_cannot_start_multiple_trips()
    {
        $driver = User::factory()->create(['role' => 'Driver']);
        
        $route = Route::create([
            'route_name' => 'Test Route',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        // Start first trip
        $this->actingAs($driver)
            ->post('/driver/trip/start', [
                'route_id' => $route->id,
                'start_lat' => 6.927079,
                'start_lng' => 80.771179,
            ]);

        // Try to start second trip
        $response = $this->actingAs($driver)
            ->post('/driver/trip/start', [
                'route_id' => $route->id,
                'start_lat' => 6.927079,
                'start_lng' => 80.771179,
            ]);

        $response->assertSessionHasErrors('error');
    }

    /**
     * Test driver can complete trip
     */
    public function test_driver_can_complete_trip()
    {
        $driver = User::factory()->create(['role' => 'Driver']);
        
        $route = Route::create([
            'route_name' => 'Test Route',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        $trip = PickupLog::create([
            'driver_id' => $driver->id,
            'route_id' => $route->id,
            'status' => 'Active',
            'start_time' => now(),
            'start_lat' => 6.927079,
            'start_lng' => 80.771179,
            'cleared_nodes' => [],
        ]);

        $response = $this->actingAs($driver)
            ->post('/driver/trip/complete', [
                'pickup_log_id' => $trip->id,
                'end_lat' => 6.924342,
                'end_lng' => 80.777869,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $trip->refresh();
        $this->assertEquals('Completed', $trip->status);
    }

    /**
     * Test GPS coordinates must be valid
     */
    public function test_trip_coordinates_must_be_valid()
    {
        $driver = User::factory()->create(['role' => 'Driver']);
        
        $route = Route::create([
            'route_name' => 'Test Route',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        $response = $this->actingAs($driver)
            ->post('/driver/trip/start', [
                'route_id' => $route->id,
                'start_lat' => 99.999, // Invalid latitude
                'start_lng' => 80.771179,
            ]);

        $response->assertSessionHasErrors('start_lat');
    }

    /**
     * Test driver can toggle cleared nodes
     */
    public function test_driver_can_toggle_cleared_node()
    {
        $driver = User::factory()->create(['role' => 'Driver']);
        
        $route = Route::create([
            'route_name' => 'Test Route',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        $trip = PickupLog::create([
            'driver_id' => $driver->id,
            'route_id' => $route->id,
            'status' => 'Active',
            'start_time' => now(),
            'start_lat' => 6.927079,
            'start_lng' => 80.771179,
            'cleared_nodes' => [],
        ]);

        $response = $this->actingAs($driver)
            ->postJson('/driver/trip/toggle-node', [
                'pickup_log_id' => $trip->id,
                'node_index' => 0,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('cleared_nodes.0', 0);
    }
}
