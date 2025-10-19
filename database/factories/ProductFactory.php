<?php

namespace Database\Factories;

use App\Models\Brand\Brand;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $category = $this->faker->randomElement(['top', 'bottom', 'outerwear', 'shoes', 'accessory']);
        $marketplace = $this->faker->randomElement(['zara', 'boss', 'massimo-dutti', 'mr-porter']);

        return [
            'brand_id' => Brand::factory(),
            'slug' => Str::slug($name.'-'.$this->faker->unique()->numerify('###')),
            'name' => Str::title($name),
            'category' => $category,
            'gender' => $this->faker->randomElement(['men', 'women', 'unisex']),
            'description' => $this->faker->sentences(2, true),
            'color_palette' => [
                'primary' => $this->faker->hexColor(),
                'secondary' => $this->faker->optional()->hexColor(),
            ],
            'price' => $this->faker->randomFloat(2, 25, 450),
            'currency' => 'USD',
            'product_url' => $this->faker->url(),
            'marketplace' => $marketplace,
            'image_url' => $this->faker->imageUrl(640, 640, 'fashion', true),
            'metadata' => [
                'materials' => $this->faker->words(2),
                'care' => $this->faker->sentence(),
            ],
            'is_active' => $this->faker->boolean(95),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
