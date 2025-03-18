<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence,
            'image_url' => $this->faker->imageUrl(),
            'category_id' => null,
            'brand_id' => null,
            'is_published' => $this->faker->boolean,
        ];
    }
}
