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

        $clothes = [
            'Футболка "Rich as"',
            'Джинсы "Levi\'s 501"',
            'Куртка "The North Face Nuptse"',
            'Худи "Nike Tech Fleece"',
            'Пуховик "Canada Goose Expedition"',
            'Кроссовки "Adidas Yeezy Boost 350"',
            'Рубашка "Ralph Lauren Oxford"',
            'Шорты "Puma Activewear"',
            'Пиджак "Hugo Boss Classic"',
            'Свитер "Gucci Monogram"',
            'Шапка "New Era 59FIFTY"',
            'Брюки "Zara Slim Fit"',
            'Леггинсы "Nike Pro"',
            'Кеды "Converse Chuck Taylor"',
            'Жилет "Moncler Grenoble"',
            'Толстовка "Supreme Box Logo"',
            'Футболка "Balenciaga Oversized"',
            'Костюм "Armani Black Label"',
            'Бомбер "Alpha Industries MA-1"',
            'Шорты "Calvin Klein Lounge"',
            'Джинсовая куртка "Wrangler Retro"',
            'Сандалии "Birkenstock Arizona"',
            'Плащ "Burberry Kensington"',
            'Брюки-карго "Carhartt WIP"',
            'Перчатки "The North Face Etip"',
        ];

        $images = [
            'https://www.pokupkalux.ru/upload/Image4text/26905/20.jpg',
            'https://i.pinimg.com/736x/50/be/d1/50bed10e27431c730175115e893be124.jpg',
            'https://i.pinimg.com/originals/4a/04/94/4a04941f85aa749ea9f9d631e4be2872.jpg',
            'https://i.pinimg.com/736x/3d/93/35/3d9335b0c2bcd03c6d20c32d02fd4f71--italian-chic-italian-fashion.jpg',
            'https://i.pinimg.com/originals/38/bd/48/38bd4886f0544ea8a5ff1673206f28a5.jpg',
            'https://i.pinimg.com/originals/a0/42/ab/a042ab081fbe0a5ca4974dfa3a94cab6.jpg',
            'https://i.pinimg.com/originals/38/44/0a/38440a9b4e767f4c18b31e651457c10c.jpg'
        ];

        $video_links = [
            "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
            "https://www.youtube.com/watch?v=3JZ_D3ELwOQ",
            "https://www.youtube.com/watch?v=kJQP7kiw5Fk",
            "https://www.youtube.com/watch?v=2Vv-BfVoq4g",
            "https://www.youtube.com/watch?v=YQHsXMglC9A",
            "https://www.youtube.com/watch?v=fLexgOxsZu0",
            "https://www.youtube.com/watch?v=LHCob76kigA",
            "https://www.youtube.com/watch?v=hT_nvWreIhg",
            "https://www.youtube.com/watch?v=RgKAFK5djSk",
            "https://www.youtube.com/watch?v=60ItHLz5WEA"
        ];

        return [
            'name' => $this->faker->randomElement($clothes),
            'category_id' => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'image_urls' => json_encode([
                $this->faker->randomElement($images),
                $this->faker->randomElement($images)
            ]),
            'video_url' => $this->faker->randomElement($video_links),
            'description' => $this->faker->sentence(15),
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
