<?php

namespace App\Filament\Resources\DeliveryServiceResource\Pages;

use App\Filament\Resources\DeliveryServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryServices extends ListRecords
{
    protected static string $resource = DeliveryServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
