<?php

use App\Models\Clothe\Clothe;
use App\Services\AI\GeminiMatchingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

it('can be instantiated with user id', function () {
    $service = new GeminiMatchingService(1);
    
    expect($service)->toBeInstanceOf(GeminiMatchingService::class);
});

it('returns empty array when no selected clothes provided', function () {
    $service = new GeminiMatchingService(1);
    
    $result = $service->findMatchingClothes(
        collect([]),
        collect([Clothe::factory()->make()])
    );
    
    expect($result)->toBeArray();
    expect($result)->toBeEmpty();
});

it('prepares clothes data correctly', function () {
    $service = new GeminiMatchingService(1);
    
    $clothes = collect([
        Clothe::factory()->make([
            'id' => 1,
            'label' => 'Test Shirt',
            'category' => 'top',
            'occasion' => 'casual',
            'season' => 'summer',
            'color_palette' => ['primary' => '#000000'],
            'ai_summary' => ['description' => 'A nice shirt'],
            'metadata' => ['fit' => 'regular'],
        ])
    ]);
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('prepareClothesData');
    $method->setAccessible(true);
    
    $result = $method->invoke($service, $clothes);
    
    expect($result)->toBeArray();
    expect($result[0])->toHaveKeys(['id', 'label', 'category', 'occasion', 'season', 'color_palette', 'ai_summary', 'metadata']);
    expect($result[0]['id'])->toBe(1);
    expect($result[0]['label'])->toBe('Test Shirt');
});

it('builds matching prompt correctly', function () {
    $service = new GeminiMatchingService(1);
    
    $selectedClothes = [
        [
            'id' => 1,
            'label' => 'Blue Shirt',
            'category' => 'top',
            'occasion' => 'casual',
        ]
    ];
    
    $availableClothes = [
        [
            'id' => 2,
            'label' => 'Black Pants',
            'category' => 'bottom',
            'occasion' => 'casual',
        ]
    ];
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('buildMatchingPrompt');
    $method->setAccessible(true);
    
    $result = $method->invoke($service, $selectedClothes, $availableClothes);
    
    expect($result)->toBeString();
    expect($result)->toContain('SELECTED CLOTHES');
    expect($result)->toContain('AVAILABLE CLOTHES');
    expect($result)->toContain('Blue Shirt');
    expect($result)->toContain('Black Pants');
});

it('normalizes matching results correctly', function () {
    $service = new GeminiMatchingService(1);
    
    $data = [
        'matches' => [
            ['clothe_id' => 1, 'match_score' => 0.8, 'reasoning' => 'Good match'],
        ],
        'outfit_suggestions' => [
            ['name' => 'Casual Outfit', 'items' => [1, 2]],
        ],
        'styling_tips' => ['Tip 1', 'Tip 2'],
    ];
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('normalizeMatchingResults');
    $method->setAccessible(true);
    
    $result = $method->invoke($service, $data);
    
    expect($result)->toBeArray();
    expect($result['matches'])->toHaveCount(1);
    expect($result['outfit_suggestions'])->toHaveCount(1);
    expect($result['styling_tips'])->toHaveCount(2);
});

it('returns default results when normalization fails', function () {
    $service = new GeminiMatchingService(1);
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('defaultMatchingResults');
    $method->setAccessible(true);
    
    $result = $method->invoke($service);
    
    expect($result)->toBeArray();
    expect($result['matches'])->toBeEmpty();
    expect($result['outfit_suggestions'])->toBeEmpty();
    expect($result['styling_tips'])->toHaveCount(1);
    expect($result['styling_tips'][0])->toContain('Unable to find matches');
});
