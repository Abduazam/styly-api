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

it('returns collection counts for authenticated user', function () {
    $user = User::factory()->create();

    Clothe::factory()->count(5)->for($user)->create();
    Outfit::factory()->count(3)->for($user)->create();

    actingAs($user);

    $response = getJson('/collections');

    $response->assertOk()
        ->assertJson([
            'data' => [
                'clothes_count' => 5,
                'outfits_count' => 3,
            ],
        ]);
});

it('redirects guests to login', function () {
    getJson('/collections')->assertRedirect('/login');
});
