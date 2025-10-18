<?php

namespace Database\Factories;

use App\Models\Clothe\Clothe;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Clothe>
 */
final class ClotheFactory extends Factory
{
    protected $model = Clothe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = $this->faker->randomElement(['top', 'bottom', 'shoes', 'outerwear', 'accessory']);

        return [
            'uuid' => (string) Str::ulid(),
            'user_id' => User::factory(),
            'label' => ucfirst($this->faker->words(2, true)),
            'category' => $category,
            'occasion' => $this->faker->randomElement(['casual', 'business', 'formal', 'party', null]),
            'season' => $this->faker->randomElement(['spring', 'summer', 'autumn', 'winter', null]),
            'color_palette' => [
                'primary' => $this->faker->hexColor(),
                'secondary' => $this->faker->hexColor(),
            ],
            'source_path' => 'wardrobe/raw/'.$this->faker->unique()->uuid.'.jpg',
            'image_path' => 'wardrobe/processed/'.$this->faker->unique()->uuid.'.jpg',
            'thumbnail_path' => 'wardrobe/thumbnails/'.$this->faker->unique()->uuid.'.jpg',
            'ai_summary' => [
                'description' => $this->faker->sentence(),
                'materials' => $this->faker->words(2),
            ],
            'metadata' => [
                'fit' => $this->faker->randomElement(['slim', 'regular', 'oversized']),
                'pattern' => $this->faker->randomElement(['solid', 'striped', 'plaid', 'graphic']),
            ],
            'is_favorite' => $this->faker->boolean(20),
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-2 months'),
        ];
    }
}
