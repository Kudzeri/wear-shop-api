<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;


    public function definition(): array
    {
        $category_images = [
            "https://i.pinimg.com/originals/41/fb/43/41fb43d3d678c7e35126b03e8ba9b00e.jpg",
            "https://i.pinimg.com/originals/4b/c6/83/4bc683bbc150c3bc8f594fede6759f4c.jpg",
            "https://avatars.mds.yandex.net/i?id=f19178e61d1bc8a31e1c0e5c1b36c810_l-10811833-images-thumbs&ref=rim&n=13&w=533&h=800",
            "https://static.tildacdn.com/tild3565-3038-4461-b933-633163343164/__2.jpg",
            "https://avatars.mds.yandex.net/i?id=3249bda4f7d010c01778153486eb3f93_l-4902967-images-thumbs&n=13"
        ];

        return [
            'slug' => $this->faker->unique()->slug(),
            'title' => $this->faker->word(),
            'category_id' => null,
            'image' => $this->faker->randomElement($category_images)
        ];
    }
}
