<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TouristAttraction;
use App\Models\Ticket;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_booking()
    {
        $user = User::factory()->create();
        $attraction = TouristAttraction::factory()->create();
        $ticket = Ticket::factory()->create(['tourist_attraction_id' => $attraction->id]);

        $this->actingAs($user);

        $response = $this->post(route('bookings.store', $attraction), [
            'visit_date' => now()->addDays(2)->toDateString(),
            'quantity' => 2,
            'notes' => 'Test booking',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'tourist_attraction_id' => $attraction->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'notes' => 'Test booking',
        ]);
    }

    public function test_booking_requires_authentication()
    {
        $attraction = TouristAttraction::factory()->create();
        $response = $this->post(route('bookings.store', $attraction), [
            'visit_date' => now()->addDays(2)->toDateString(),
            'quantity' => 1,
        ]);
        $response->assertRedirect('/login');
    }
} 