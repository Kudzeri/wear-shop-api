<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory(100)->create()->each(function (Product $product) {
            $colors = Color::inRandomOrder()->limit(rand(1, 3))->get();
            $product->colors()->attach($colors);

            $size = Size::inRandomOrder()->limit(rand(1, 3))->get();
            $product->sizes()->attach($size);

        });
    }
}
