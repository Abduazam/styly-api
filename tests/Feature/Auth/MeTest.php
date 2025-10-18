<?php

use App\Models\Clothe\Clothe;
use App\Models\Outfit\Outfit;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'database']);
});

it('returns profile snapshot for authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'Agent Example',
        'email' => 'agent@example.com',
    ]);

    Clothe::factory()->count(3)->for($user)->create();
    Outfit::factory()->count(2)->for($user)->create();

    actingAs($user);

    $response = getJson('/me');

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'Agent Example',
                'email' => 'agent@example.com',
                'clothes_count' => 3,
                'outfits_count' => 2,
            ],
        ]);
});

it('rejects unauthenticated request', function () {
    getJson('/me')->assertRedirect('/login');
});
