<?php

use App\Models\Clothe\Clothe;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'database']);
});

it('lists clothes belonging to the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Clothe::factory()->count(3)->for($user)->create();
    Clothe::factory()->count(2)->for($otherUser)->create();

    actingAs($user);

    $response = getJson('/collections/wardrobe');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonCount(3, 'data');
});

it('stores a new clothe image for processing', function () {
    $user = User::factory()->create();
    Storage::fake('public');

    actingAs($user);

    $file = UploadedFile::fake()->image('jacket-blue.jpg', 1080, 1440);

    $response = post('/collections/wardrobe', [
        'image' => $file,
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user_id', $user->id)
        ->assertJsonPath('data.metadata.ingestion_status', 'pending');

    $storedPath = $response->json('data.source_path');
    Storage::disk('public')->assertExists($storedPath);
});

it('validates image presence when storing', function () {
    $user = User::factory()->create();

    actingAs($user);

    $response = post('/collections/wardrobe', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});
