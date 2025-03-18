<?php

namespace App\Exports;

use App\Models\Contract;

class ExportContract
{
    public function getData(): array
    {
        $contracts = Contract::all();
        return $contracts->toArray();
    }

    public function getHeadings(): array
    {
        return ['ID', 'Contract Number', 'Start Date', 'End Date'];
    }
}
