<?php

namespace App\Exports;

use App\Models\Product;

class ProductsExport
{

    public function getData(): array
    {
        $products = Product::select('id', 'name', 'price', 'stock')->get();
        return $products->toArray();
    }

    public function getHeadings(): array
    {
        return ['ID', 'Название', 'Цена', 'Остаток'];
    }
}
