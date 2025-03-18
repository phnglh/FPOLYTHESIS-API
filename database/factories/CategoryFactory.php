<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $name = $this->faker->unique()->word;
        $slug = Str::slug($name);

        while (Category::where('slug', $slug)->exists()) {
            $name = $this->faker->unique()->word;
            $slug = Str::slug($name);
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->sentence,
            'parent_id' => null,
        ];
    }

    public function withParent()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => Category::inRandomOrder()->first()->id,
            ];
        });
    }
}
