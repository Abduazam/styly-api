<?php

use App\Models\Clothe\Clothe;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'database']);
});

it('finds matching clothes for selected items', function () {
    $user = User::factory()->create();
    
    // Create some clothes for the user
    $selectedClothes = Clothe::factory()->count(2)->for($user)->create([
        'category' => 'top',
        'occasion' => 'casual',
    ]);
    
    $availableClothes = Clothe::factory()->count(3)->for($user)->create([
        'category' => 'bottom',
        'occasion' => 'casual',
    ]);

    actingAs($user);

    $response = postJson('/api/matching/find-matches', [
        'selected_clothes' => $selectedClothes->pluck('id')->toArray(),
        'use_visual_analysis' => false,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'success',
                'selected_clothes',
                'matching_results' => [
                    'matches',
                    'outfit_suggestions',
                    'styling_tips',
                ],
                'total_available',
                'analysis_type',
            ],
        ]);
});

it('validates selected clothes belong to user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    
    $otherUserClothes = Clothe::factory()->count(2)->for($otherUser)->create();

    actingAs($user);

    $response = postJson('/api/matching/find-matches', [
        'selected_clothes' => $otherUserClothes->pluck('id')->toArray(),
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Some selected clothes do not belong to you.',
        ]);
});

it('requires at least one selected clothe', function () {
    $user = User::factory()->create();

    actingAs($user);

    $response = postJson('/api/matching/find-matches', [
        'selected_clothes' => [],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['selected_clothes']);
});

it('generates outfit suggestions with filters', function () {
    $user = User::factory()->create();
    
    $selectedClothes = Clothe::factory()->count(1)->for($user)->create([
        'category' => 'top',
        'occasion' => 'business',
    ]);
    
    $availableClothes = Clothe::factory()->count(2)->for($user)->create([
        'category' => 'bottom',
        'occasion' => 'business',
    ]);

    actingAs($user);

    $response = postJson('/api/matching/outfit-suggestions', [
        'selected_clothes' => $selectedClothes->pluck('id')->toArray(),
        'occasion' => 'business',
        'season' => 'winter',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'success',
                'selected_clothes',
                'matching_results' => [
                    'outfit_suggestions',
                ],
            ],
        ]);
});

it('provides styling tips for selected clothes', function () {
    $user = User::factory()->create();
    
    $selectedClothes = Clothe::factory()->count(2)->for($user)->create([
        'category' => 'top',
        'color_palette' => ['primary' => '#000000', 'secondary' => '#FFFFFF'],
    ]);

    actingAs($user);

    $response = postJson('/api/matching/styling-tips', [
        'selected_clothes' => $selectedClothes->pluck('id')->toArray(),
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'selected_clothes',
                'styling_tips',
            ],
        ]);
});

it('handles empty wardrobe gracefully', function () {
    $user = User::factory()->create();
    
    $selectedClothes = Clothe::factory()->count(1)->for($user)->create();

    actingAs($user);

    $response = postJson('/api/matching/find-matches', [
        'selected_clothes' => $selectedClothes->pluck('id')->toArray(),
    ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'success' => true,
                'total_available' => 0,
            ],
        ]);
});

it('supports visual analysis mode', function () {
    $user = User::factory()->create();
    
    $selectedClothes = Clothe::factory()->count(1)->for($user)->create();
    $availableClothes = Clothe::factory()->count(2)->for($user)->create();

    actingAs($user);

    $response = postJson('/api/matching/find-matches', [
        'selected_clothes' => $selectedClothes->pluck('id')->toArray(),
        'use_visual_analysis' => true,
    ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'analysis_type' => 'visual',
            ],
        ]);
});
