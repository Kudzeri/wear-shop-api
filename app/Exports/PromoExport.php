<?php

namespace App\Exports;

use App\Models\Promo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class PromoExport implements FromCollection
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection(): Collection
    {
        $query = Promo::query();
        foreach ($this->filters as $key => $value) {
            $query->where($key, $value);
        }
        return $query->get(['id_promo', 'discount_size', 'discount_percentage', 'discount_product', 'id_promo_1c', 'ready_promo']);
    }
}
