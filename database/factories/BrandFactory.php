<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word;
        $slug = Str::slug($name);

        while (Brand::where('slug', $slug)->exists()) {
            $name = $this->faker->unique()->word;
            $slug = Str::slug($name);
        };
        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->sentence,
        ];
    }
}
