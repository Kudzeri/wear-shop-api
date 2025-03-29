<?php

namespace App\Filament\Resources\StylistResource\Pages;

use App\Filament\Resources\StylistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStylists extends ListRecords
{
    protected static string $resource = StylistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
