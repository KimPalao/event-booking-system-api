<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'token',
        ]);
    }

    public function test_retgister_with_missing_fields() {
        $response = $this->postJson('/api/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(422);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'phone' => '1234567890',
        ]);
        $response->assertStatus(422);
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
        ]);
        $response->assertStatus(422);
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(422);
    }

    public function test_register_existing_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(201);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(422);
    }

    public function test_login(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
        ]);
    }

    public function test_login_without_account(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_with_wrong_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_logout_while_not_logged_in(): void
    {
        $response = $this->postJson('/api/logout', [
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(401);
    }

    public function test_me_while_not_logged_in(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

     public function test_me(): void
     {
        $this->postJson('/api/register', [
             'name' => 'Test User',
             'email' => 'test@example.com',
             'password' => 'password123',
             'phone' => '1234567890',
        ]);

        $response = $this->postJson('/api/login', [
             'email' => 'test@example.com',
             'password' => 'password123',
        ]);
        $token = $response->json('token');

        $response = $this->getJson('/api/me', ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'name',
            'email',
            'phone',
        ]);
     }
}
