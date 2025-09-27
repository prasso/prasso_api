<?php

namespace Tests\Feature;

use Tests\TestCase;
use Prasso\Church\Models\Event;
use Prasso\Church\Models\Ministry;
use Prasso\Church\Models\EventOccurrence;
use Prasso\Church\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Create a test ministry
        $this->ministry = Ministry::create([
            'name' => 'Test Ministry',
            'description' => 'Test Ministry Description',
        ]);
    }

    /** @test */
    public function it_can_create_an_event()
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => '2025-10-01',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'location' => 'Test Location',
            'type' => 'meeting',
            'status' => 'published',
            'ministry_id' => $this->ministry->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Test Event',
                'status' => 'published',
            ]);

        $this->assertDatabaseHas('chm_events', [
            'title' => 'Test Event',
            'status' => 'published',
        ]);
    }

    /** @test */
    public function it_can_create_a_recurring_event()
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Recurring Test Event',
            'description' => 'Weekly meeting',
            'start_date' => '2025-10-01',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'location' => 'Test Location',
            'type' => 'meeting',
            'status' => 'published',
            'recurrence_pattern' => 'weekly',
            'recurrence_interval' => 1,
            'recurrence_days' => [2, 4], // Tuesday, Thursday
            'end_date' => '2025-10-31',
            'ministry_id' => $this->ministry->id,
        ]);

        $response->assertStatus(201);

        // Verify the event was created
        $this->assertDatabaseHas('chm_events', [
            'title' => 'Recurring Test Event',
            'recurrence_pattern' => 'weekly',
        ]);

        // Verify occurrences were created
        $event = Event::first();
        $this->assertCount(9, $event->occurrences); // 2 days/week for ~4.5 weeks
    }

    /** @test */
    public function it_can_record_attendance()
    {
        $event = Event::create([
            'title' => 'Test Event',
            'start_date' => '2025-10-01',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'type' => 'service',
            'status' => 'published',
            'created_by' => $this->user->id,
        ]);

        $occurrence = $event->occurrences()->create([
            'date' => '2025-10-01',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'scheduled',
        ]);

        $response = $this->postJson("/api/occurrences/{$occurrence->id}/attendance", [
            'member_id' => 1, // Assuming member with ID 1 exists
            'status' => 'present',
            'check_in_time' => '2025-10-01 09:55:00',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('chm_attendances', [
            'event_occurrence_id' => $occurrence->id,
            'status' => 'present',
        ]);
    }

    /** @test */
    public function it_can_generate_recurrence_rule()
    {
        $event = new Event([
            'title' => 'Test RRule',
            'start_date' => '2025-01-01',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'recurrence_pattern' => 'weekly',
            'recurrence_interval' => 2,
            'recurrence_days' => [0, 2], // Sunday, Tuesday
            'end_date' => '2025-01-31',
        ]);

        $rrule = $event->getRecurrenceRule();
        $this->assertInstanceOf(\RRule\RRule::class, $rrule);
        
        $occurrences = $rrule->getOccurrences();
        $this->assertCount(5, $occurrences); // 5 occurrences in January 2025
    }
}
