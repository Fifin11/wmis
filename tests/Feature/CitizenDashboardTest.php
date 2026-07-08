<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CitizenReport;
use App\Models\RecyclingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test citizen can submit report
     */
    public function test_citizen_can_submit_report()
    {
        $citizen = User::factory()->create(['role' => 'Citizen']);
        
        $response = $this->actingAs($citizen)
            ->post('/citizen/report', [
                'issue_type' => 'Missed Pickup',
                'location_lat' => 6.927079,
                'location_lng' => 80.771179,
                'description' => 'The garbage truck did not collect waste from my area',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('citizen_reports', [
            'citizen_id' => $citizen->id,
            'issue_type' => 'Missed Pickup',
            'status' => 'Open',
        ]);
    }

    /**
     * Test guest (anonymous) can submit report
     */
    public function test_guest_can_submit_report()
    {
        $response = $this->post('/citizen/report', [
            'issue_type' => 'Illegal Dumping',
            'location_lat' => 6.927079,
            'location_lng' => 80.771179,
            'description' => 'Found illegal dumping site near my neighborhood',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('citizen_reports', [
            'citizen_id' => null,
            'issue_type' => 'Illegal Dumping',
        ]);
    }

    /**
     * Test report description must be valid
     */
    public function test_report_description_must_be_minimum_length()
    {
        $citizen = User::factory()->create(['role' => 'Citizen']);
        
        $response = $this->actingAs($citizen)
            ->post('/citizen/report', [
                'issue_type' => 'Missed Pickup',
                'location_lat' => 6.927079,
                'location_lng' => 80.771179,
                'description' => 'Too short', // Less than 10 chars
            ]);

        $response->assertSessionHasErrors('description');
    }

    /**
     * Test citizen can submit recycling claim (once per day)
     */
    public function test_citizen_can_submit_recycling_claim()
    {
        $citizen = User::factory()->create(['role' => 'Citizen']);
        
        $response = $this->actingAs($citizen)
            ->post('/citizen/recycle', [
                'description' => 'I collected and sorted 25kg of plastic bottles and cartons today',
                'claimed_points' => 15,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('recycling_submissions', [
            'citizen_id' => $citizen->id,
            'claimed_points' => 15,
            'status' => 'Pending',
        ]);
    }

    /**
     * Test citizen cannot submit multiple recycling claims per day
     */
    public function test_citizen_cannot_submit_multiple_claims_per_day()
    {
        $citizen = User::factory()->create(['role' => 'Citizen']);
        
        // First submission
        $this->actingAs($citizen)
            ->post('/citizen/recycle', [
                'description' => 'First submission of the day with adequate description',
                'claimed_points' => 10,
            ]);

        // Try second submission same day
        $response = $this->actingAs($citizen)
            ->post('/citizen/recycle', [
                'description' => 'Second submission attempt with adequate description',
                'claimed_points' => 10,
            ]);

        $response->assertSessionHasErrors('recycle');
    }

    /**
     * Test recycling points must be within valid range
     */
    public function test_recycling_points_must_be_valid_range()
    {
        $citizen = User::factory()->create(['role' => 'Citizen']);
        
        $response = $this->actingAs($citizen)
            ->post('/citizen/recycle', [
                'description' => 'This is a long enough description for recycling claim',
                'claimed_points' => 100, // Exceeds max of 50
            ]);

        $response->assertSessionHasErrors('claimed_points');
    }

    /**
     * Test citizen can view dashboard
     */
    public function test_citizen_can_view_dashboard()
    {
        $citizen = User::factory()->create(['role' => 'Citizen']);
        
        $response = $this->actingAs($citizen)
            ->get('/citizen/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('citizen.dashboard');
    }

    /**
     * Test guest can view citizen dashboard (with limited features)
     */
    public function test_guest_can_view_citizen_dashboard()
    {
        $response = $this->get('/citizen/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('citizen.dashboard');
    }

    /**
     * Test active trucks API endpoint returns proper format
     */
    public function test_active_trucks_api_returns_proper_format()
    {
        $response = $this->getJson('/api/active-trucks');

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }
}
