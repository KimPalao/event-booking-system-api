<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_ticket_as_anonymous(): void
    {
        $response = $this->postJson('/api/events/999/tickets', [
            'quantity' => 2,
            'price' => 50,
            'type' => 'standard',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_ticket_as_customer(): void
    {
        $organizer = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $organizer->id,
        ]);
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson('/api/events/' . $event->id . '/tickets', [
            'quantity' => 2,
            'type' => 'standard',
            'price' => 50,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_create_ticket_as_organizer(): void
    {
        $user = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $user->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson('/api/events/' . $event->id . '/tickets', [
            'quantity' => 2,
            'price' => 50,
            'type' => 'standard',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
    }

    public function test_create_ticket_as_admin(): void
    {
        $user = User::factory()->isAdmin()->create();
        $event = Event::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson('/api/events/' . $event->id . '/tickets', [
            'quantity' => 2,
            'type' => 'standard',
            'price' => 50,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
    }

    public function test_create_ticket_for_nonexistent_event(): void
    {
        $user = User::factory()->isOrganizer()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson('/api/events/999/tickets', [
            'quantity' => 2,
            'type' => 'standard',
            'price' => 50,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(404);
    }

    public function test_update_ticket_as_customer(): void
    {
        $organizer = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $organizer->id,
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'quantity' => 5,
            'price' => 100,
            'type' => 'vip',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_update_ticket_as_organizer(): void
    {
        $user = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $user->id,
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'quantity' => 5,
            'price' => 100,
            'type' => 'vip',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }

    public function test_update_ticket_as_admin(): void
    {
        $user = User::factory()->isAdmin()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'quantity' => 5,
            'price' => 100,
            'type' => 'vip',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }

    public function test_update_ticket_as_wrong_organizer(): void
    {
        $organizer1 = User::factory()->isOrganizer()->create();
        $organizer2 = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $organizer1->id,
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $organizer2->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'quantity' => 5,
            'price' => 100,
            'type' => 'vip',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_delete_ticket_as_customer(): void
    {
        $organizer = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $organizer->id,
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->deleteJson("/api/tickets/{$ticket->id}", [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_delete_ticket_as_organizer(): void
    {
        $user = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $user->id,
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->deleteJson("/api/tickets/{$ticket->id}", [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(204);
    }

    public function test_delete_ticket_as_admin(): void
    {
        $user = User::factory()->isAdmin()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->deleteJson("/api/tickets/{$ticket->id}", [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(204);
    }

    public function test_delete_ticket_as_wrong_organizer(): void
    {
        $organizer1 = User::factory()->isOrganizer()->create();
        $organizer2 = User::factory()->isOrganizer()->create();
        $event = Event::factory()->create([
            'created_by' => $organizer1->id,
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $organizer2->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->deleteJson("/api/tickets/{$ticket->id}", [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }
}
