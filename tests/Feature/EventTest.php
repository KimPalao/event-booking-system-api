<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Event;
use DateTime;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_create_event_as_anonymous(): void
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Test Event',
            'description' => 'This is a test event.',
            'date' => '2024-12-31',
            'location' => 'Test Location',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_event_as_customer(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson('/api/events', [
            'title' => 'Test Event',
            'description' => 'This is a test event.',
            'date' => '2024-12-31',
            'location' => 'Test Location',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_create_event_as_organizer(): void
    {
        $user = User::factory()->isOrganizer()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson('/api/events', [
            'title' => 'Test Event',
            'description' => 'This is a test event.',
            'date' => '2024-12-31',
            'location' => 'Test Location',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
    }

    public function test_create_event_as_admin(): void
    {
        $user = User::factory()->isAdmin()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->postJson('/api/events', [
            'title' => 'Test Event',
            'description' => 'This is a test event.',
            'date' => '2024-12-31',
            'location' => 'Test Location',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
    }

    public function test_update_event_as_customer(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $event = Event::factory()->create([
            'created_by' => $user->id,
        ]);
        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Test Event',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_update_event_as_organizer(): void
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
        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Test Event',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }

    public function test_update_event_as_admin(): void
    {
        $user = User::factory()->isAdmin()->create();
        $event = Event::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Test Event',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }

    public function test_update_event_as_wrong_organizer(): void
    {
        $organizer1 = User::factory()->isOrganizer()->create();
        $organizer2 = User::factory()->isOrganizer()->create();
        $event1 = Event::factory()->create([
            'created_by' => $organizer1->id,
        ]);

        // Organizer 2 tries to update the event created by Organizer 1
        $response = $this->postJson('/api/login', [
            'email' => $organizer2->email,
            'password' => 'password',
        ]);
        $token2 = $response->json('token');
        $response = $this->putJson("/api/events/{$event1->id}", [
            'title' => 'Updated Test Event',
        ], ['Authorization' => 'Bearer ' . $token2]);

        $response->assertStatus(403);
    }

    public function test_view_event(): void
    {
        User::factory()->isOrganizer()->create();
        $event = Event::factory()->create();
        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'created_by' => $event->created_by,
        ]);

        $this->assertEquals($response->json('date'), $event->date->format('Y-m-d H:i:s'));
    }

    public function test_non_existent_event(): void
    {
        $response = $this->getJson('/api/events/999');

        $response->assertStatus(404);
    }

    public function test_delete_event_as_customer(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $event = Event::factory()->create([
            'created_by' => $user->id,
        ]);
        $response = $this->deleteJson("/api/events/{$event->id}", [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_delete_event_as_organizer(): void
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
        $response = $this->deleteJson("/api/events/{$event->id}", [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(204);
    }

    public function test_delete_event_as_admin(): void
    {
        $user = User::factory()->isAdmin()->create();
        $event = Event::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('token');
        $response = $this->deleteJson("/api/events/{$event->id}", [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(204);
    }

    public function test_delete_event_as_wrong_organizer(): void
    {
        $organizer1 = User::factory()->isOrganizer()->create();
        $organizer2 = User::factory()->isOrganizer()->create();
        $event1 = Event::factory()->create([
            'created_by' => $organizer1->id,
        ]);

        // Organizer 2 tries to delete the event created by Organizer 1
        $response = $this->postJson('/api/login', [
            'email' => $organizer2->email,
            'password' => 'password',
        ]);
        $token2 = $response->json('token');
        $response = $this->deleteJson("/api/events/{$event1->id}", [], ['Authorization' => 'Bearer ' . $token2]);

        $response->assertStatus(403);
    }

    public function test_list_events(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->count(5)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_list_title_search(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->create(['title' => 'Laravel Conference']);
        Event::factory()->create(['title' => 'Vue.js Workshop']);
        Event::factory()->create(['title' => 'React Summit']);

        $response = $this->getJson('/api/events?title=Laravel');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Laravel Conference']);
    }

    public function test_list_pagination(): void
    {
        User::factory()->isOrganizer()->create();
        Event::factory()->count(15)->create();

        $response = $this->getJson('/api/events?page=2&rows=5');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }
}
