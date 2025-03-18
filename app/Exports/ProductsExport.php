<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsExport implements FromCollection
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection(): Collection
    {
        $query = Product::query();
        foreach ($this->filters as $key => $value) {
            $query->where($key, $value);
        }
        return $query->get(['id_product', 'id_product_1c', 'title', 'article', 'unit', 'price', 'weight', 'length', 'width', 'height', 'ready']);
    }
}
