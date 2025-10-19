<?php

namespace Database\Factories;

use App\Models\Brand\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
final class BrandFactory extends Factory
{
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'slug' => Str::slug($name.'-'.$this->faker->unique()->lexify('???')),
            'name' => $name,
            'description' => $this->faker->optional()->sentences(2, true),
            'website_url' => $this->faker->optional()->url(),
            'logo_url' => $this->faker->optional()->imageUrl(400, 400, 'fashion', true),
            'metadata' => [
                'country' => $this->faker->countryCode(),
                'founded' => $this->faker->numberBetween(1950, 2020),
            ],
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
