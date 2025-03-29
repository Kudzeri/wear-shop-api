<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Основные категории
        $categories = [
            'clothing' => 'Одежда',
            'shoes' => 'Обувь',
            'accessories' => 'Аксессуары',
            'sportswear' => 'Спортивная одежда',
            'outerwear' => 'Верхняя одежда',
            'formalwear' => 'Деловая одежда',
            'underwear' => 'Нижнее бельё',
        ];

        // Создаём основные категории
        $createdCategories = [];
        foreach ($categories as $slug => $title) {
            $createdCategories[$slug] = Category::factory()->create([
                'slug' => $slug,
                'title' => $title,
                'category_id' => null,
            ]);
        }

        // Подкатегории для каждой основной категории
        $subcategories = [
            'clothing' => [
                't-shirts' => 'Футболки',
                'jeans' => 'Джинсы',
                'jackets' => 'Куртки',
                'sweaters' => 'Свитеры',
                'shorts' => 'Шорты',
            ],
            'shoes' => [
                'sneakers' => 'Кроссовки',
                'boots' => 'Ботинки',
                'sandals' => 'Сандалии',
                'loafers' => 'Лоферы',
            ],
            'accessories' => [
                'hats' => 'Головные уборы',
                'bags' => 'Сумки',
                'belts' => 'Ремни',
                'watches' => 'Часы',
            ],
            'sportswear' => [
                'tracksuits' => 'Спортивные костюмы',
                'leggings' => 'Леггинсы',
                'sport-shoes' => 'Спортивная обувь',
            ],
            'outerwear' => [
                'coats' => 'Пальто',
                'raincoats' => 'Плащи',
            ],
            'formalwear' => [
                'suits' => 'Костюмы',
                'dress-shirts' => 'Рубашки',
            ],
            'underwear' => [
                'socks' => 'Носки',
                'panties' => 'Трусы',
            ],
        ];

        // Создаём подкатегории
        foreach ($subcategories as $parentSlug => $children) {
            foreach ($children as $slug => $title) {
                Category::factory()->create([
                    'slug' => $slug,
                    'title' => $title,
                    'category_id' => $createdCategories[$parentSlug]->id,
                ]);
            }
        }
    }
}
