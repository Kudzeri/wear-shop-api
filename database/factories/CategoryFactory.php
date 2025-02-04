<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(),
            'title' => $this->faker->word(),
            'category_id' => null,
        ];
    }
}
