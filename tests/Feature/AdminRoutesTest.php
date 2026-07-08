<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Route;
use App\Models\SystemLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that unauthenticated users cannot access admin dashboard
     */
    public function test_unauthenticated_users_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated admin can access dashboard
     */
    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * Test that drivers cannot access admin dashboard
     */
    public function test_driver_cannot_access_admin_dashboard()
    {
        $driver = User::factory()->create(['role' => 'Driver']);
        $response = $this->actingAs($driver)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test admin can create a new route
     */
    public function test_admin_can_create_route()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        
        $routeData = [
            'route_name' => 'Zone Alpha - Downtown',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => json_encode([
                ['lat' => 6.927079, 'lng' => 80.771179],
                ['lat' => 6.924342, 'lng' => 80.777869],
                ['lat' => 6.920000, 'lng' => 80.775000],
            ]),
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/routes', $routeData);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('routes', [
            'route_name' => 'Zone Alpha - Downtown',
            'waste_type' => 'Organic',
        ]);
    }

    /**
     * Test route name must be unique
     */
    public function test_route_name_must_be_unique()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        Route::create([
            'route_name' => 'Zone Alpha',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        $routeData = [
            'route_name' => 'Zone Alpha', // Duplicate
            'waste_type' => 'Plastic',
            'scheduled_day' => 'Tuesday',
            'zone_coordinates' => json_encode([[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]]),
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/routes', $routeData);

        $response->assertSessionHasErrors('route_name');
    }

    /**
     * Test polygon must have at least 3 points
     */
    public function test_polygon_must_have_minimum_3_points()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        
        $routeData = [
            'route_name' => 'Zone Beta',
            'waste_type' => 'Plastic',
            'scheduled_day' => 'Wednesday',
            'zone_coordinates' => json_encode([
                ['lat' => 6.927079, 'lng' => 80.771179],
                ['lat' => 6.924342, 'lng' => 80.777869],
            ]), // Only 2 points
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/routes', $routeData);

        $response->assertSessionHasErrors('zone_coordinates');
    }

    /**
     * Test admin can create driver account
     */
    public function test_admin_can_create_driver()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        
        $driverData = [
            'name' => 'John Driver',
            'email' => 'john@driver.com',
            'phone' => '+94 71 123 4567',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/drivers', $driverData);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@driver.com',
            'role' => 'Driver',
        ]);
    }

    /**
     * Test driver password must be strong
     */
    public function test_driver_password_must_be_strong()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        
        $driverData = [
            'name' => 'Weak Password Driver',
            'email' => 'weak@driver.com',
            'phone' => '+94 71 123 4567',
            'password' => 'weakpass', // No uppercase, no number
            'password_confirmation' => 'weakpass',
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/drivers', $driverData);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test admin can search routes
     */
    public function test_admin_can_search_routes()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        
        Route::create([
            'route_name' => 'Zone Alpha Organic',
            'waste_type' => 'Organic',
            'scheduled_day' => 'Monday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        Route::create([
            'route_name' => 'Zone Beta Plastic',
            'waste_type' => 'Plastic',
            'scheduled_day' => 'Tuesday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/dashboard?route_search=Alpha');

        $response->assertStatus(200);
        $response->assertSee('Zone Alpha Organic');
        $response->assertDontSee('Zone Beta Plastic');
    }

    /**
     * Test admin can delete route
     */
    public function test_admin_can_delete_route()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        
        $route = Route::create([
            'route_name' => 'Zone To Delete',
            'waste_type' => 'Paper',
            'scheduled_day' => 'Friday',
            'zone_coordinates' => [[6.927, 80.771], [6.924, 80.777], [6.920, 80.775]],
        ]);

        $response = $this->actingAs($admin)
            ->delete('/admin/routes/' . $route->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('routes', ['id' => $route->id]);
    }
}
