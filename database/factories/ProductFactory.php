<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Футболка', 'Джинсы', 'Куртка', 'Шорты', 'Платье']),
            'category_id' => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'image_urls' => json_encode([
                $this->faker->imageUrl(640, 480, 'fashion'),
                $this->faker->imageUrl(640, 480, 'fashion')
            ]),
            'video_url' => $this->faker->optional()->url(),
            'description' => $this->faker->sentence(10),
            'composition_care' => '100% хлопок, машинная стирка при 30°',
            'preference' => json_encode([
                'S' => ['длина' => 60, 'обхват_груди' => 90],
                'M' => ['длина' => 62, 'обхват_груди' => 94],
                'L' => ['длина' => 64, 'обхват_груди' => 98]
            ]),
            'measurements' => json_encode([
                'S' => ['длина' => 60, 'обхват_груди' => 90]
            ])
        ];
    }
}
