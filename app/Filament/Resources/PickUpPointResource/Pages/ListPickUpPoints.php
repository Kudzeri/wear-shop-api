<?php

namespace App\Filament\Resources\PickUpPointResource\Pages;

use App\Filament\Resources\PickUpPointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPickUpPoints extends ListRecords
{
    protected static string $resource = PickUpPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
