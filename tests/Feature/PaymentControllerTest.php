<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_payment_as_anonymous(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->create();
        Ticket::factory()->create();
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
        ]);
        $response = $this->postJson("/api/bookings/{$booking->id}/payment");

        $response->assertStatus(401);
    }

    public function test_create_payment_as_customer(): void
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
        $response = $this->postJson("/api/bookings/{$booking->id}/payment", [], ['Authorization' => 'Bearer ' . $token]);
    }

    public function test_view_payment_as_customer(): void
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
        $response = $this->postJson("/api/bookings/{$booking->id}/payment", [], ['Authorization' => 'Bearer ' . $token]);
        $paymentId = $response->json('data.id');
        $response = $this->getJson("/api/payments/{$paymentId}", ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }
}
