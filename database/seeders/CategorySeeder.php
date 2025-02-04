<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $clothing = Category::factory()->create([
            'slug' => 'clothing',
            'title' => 'Одежда',
            'category_id' => null,
        ]);

        $tShirts = Category::factory()->create(['slug' => 't-shirts', 'title' => 'Футболки', 'category_id' => $clothing->id]);
        $jeans = Category::factory()->create(['slug' => 'jeans', 'title' => 'Джинсы', 'category_id' => $clothing->id]);
        $jackets = Category::factory()->create(['slug' => 'jackets', 'title' => 'Куртки', 'category_id' => $clothing->id]);
        $shoes = Category::factory()->create(['slug' => 'shoes', 'title' => 'Обувь', 'category_id' => $clothing->id]);
    }
}
