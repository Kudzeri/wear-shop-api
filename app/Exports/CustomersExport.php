<?php

namespace App\Exports;

use App\Models\User;

class CustomersExport
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function getData(): array
    {
        $customers = User::query()
            ->when(isset($this->filters['id_customer']), function($query) {
                $query->where('id', $this->filters['id_customer']);
            })
            ->get(['id', 'name', 'email']);
        return $customers->toArray();
    }

    public function getHeadings(): array
    {
        return ['ID', 'Name', 'Email'];
    }
}
