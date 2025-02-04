<?php

namespace Database\Factories;

use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SizeFactory extends Factory
{
    protected $model = Size::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
