<?php

namespace Database\Factories;

use App\Models\Outfit\Outfit;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Outfit>
 */
final class OutfitFactory extends Factory
{
    protected $model = Outfit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tag = $this->faker->randomElement(['wardrobe', 'featured']);

        return [
            'uuid' => (string) Str::ulid(),
            'user_id' => User::factory(),
            'title' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->sentence(),
            'tag' => $tag,
            'status' => $this->faker->randomElement(['draft', 'published']),
            'image_path' => 'outfits/generated/'.$this->faker->unique()->uuid.'.jpg',
            'thumbnail_path' => 'outfits/thumbnails/'.$this->faker->unique()->uuid.'.jpg',
            'prompt' => [
                'mode' => $tag === 'wardrobe' ? 'wardrobe_match' : 'new_style',
                'inputs' => $this->faker->words(3),
            ],
            'metadata' => [
                'rating' => $this->faker->numberBetween(1, 5),
                'generated_by' => 'gemini',
            ],
            'is_favorite' => $this->faker->boolean(20),
            'generated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
