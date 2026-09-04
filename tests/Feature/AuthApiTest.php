<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_a_user_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Account created successfully.')
            ->assertJsonPath('data.user.name', 'Budi')
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_register_requires_unique_email(): void
    {
        User::query()->create([
            'name' => 'First',
            'email' => 'taken@example.com',
            'password' => bcrypt('something'),
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Second',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_it_logs_a_user_in_with_its_token(): void
    {
        User::query()->create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'budi@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'budi@example.com')
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_login_rejects_wrong_credentials(): void
    {
        User::query()->create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => bcrypt('right-password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'budi@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = User::query()->create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'budi@example.com');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::query()->create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
