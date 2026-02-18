<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_booking_as_anonymous(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->create();
        $ticket = Ticket::factory()->create();
        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 2,
        ]);

        $response->assertStatus(401);
    }

    public function test_create_booking_as_customer(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->create();
        $ticket = Ticket::factory()->create();
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 2,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
    }
    public function test_create_booking_as_organizer(): void
    {
        $user = User::factory()->isOrganizer()->create();
        Event::factory()->create();
        $ticket = Ticket::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 2,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_cancel_booking_as_customer(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->create();
        Ticket::factory()->create();
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->putJson('/api/bookings/' . $booking->id . '/cancel', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    public function test_cancel_booking_as_wrong_customer(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->create();
        Ticket::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user1->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user2->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->putJson('/api/bookings/' . $booking->id . '/cancel', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_get_bookings(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->create();
        Ticket::factory()->create();
        $user = User::factory()->create();
        Booking::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->getJson('/api/bookings', ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_get_bookings_as_organizer(): void
    {
        $user = User::factory()->isOrganizer()->create();
        Event::factory()->create();
        Ticket::factory()->create();
        Booking::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->getJson('/api/bookings', ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }
}
