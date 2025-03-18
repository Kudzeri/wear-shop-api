<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class CustomersExport implements FromCollection
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection(): Collection
    {
        $query = Customer::query();
        // Применяем фильтры, если заданы
        foreach ($this->filters as $key => $value) {
            $query->where($key, $value);
        }
        return $query->get(['id_customer', 'id_customer_1c', 'name', 'surname', 'email', 'phone']);
    }
}
