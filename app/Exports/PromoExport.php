<?php

namespace App\Exports;

use App\Models\Promo;

class PromoExport
{
    public function getData(): array
    {
        $promos = Promo::select('id', 'code', 'discount', 'expires_at')->get();
        return $promos->toArray();
    }

    public function getHeadings(): array
    {
        return ['ID', 'Промо-код', 'Скидка', 'Действует до'];
    }
}
