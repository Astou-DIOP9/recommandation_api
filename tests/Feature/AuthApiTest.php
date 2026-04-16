<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Astou Dev',
            'email' => 'astoudev0@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email'],
            ]);
    }

    public function test_user_can_login_and_access_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('token');

        $loginResponse
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('email', 'test@example.com');
    }

    public function test_user_can_logout(): void
    {
        $plainToken = str_repeat('a', 64);

        $user = User::factory()->create([
            'api_token' => hash('sha256', $plainToken),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Deconnexion reussie');

        $this->assertNull($user->fresh()->api_token);
    }
}
