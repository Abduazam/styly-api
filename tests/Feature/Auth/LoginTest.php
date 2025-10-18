<?php

use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'database']);
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'agent@example.com',
        'name' => 'Agent Example',
    ]);

    $response = postJson('/auth/login', [
        'email' => 'agent@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', 'agent@example.com')
        ->assertJsonPath('data.name', 'Agent Example');

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('sessions', [
        'user_id' => $user->id,
    ]);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'agent@example.com',
    ]);

    $response = postJson('/auth/login', [
        'email' => 'agent@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid credentials.']);

    $this->assertGuest();
    $this->assertDatabaseMissing('sessions', [
        'user_id' => $user->id,
    ]);
});
