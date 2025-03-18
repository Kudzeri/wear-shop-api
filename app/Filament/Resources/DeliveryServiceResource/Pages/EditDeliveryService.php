<?php

namespace App\Filament\Resources\DeliveryServiceResource\Pages;

use App\Filament\Resources\DeliveryServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryService extends EditRecord
{
    protected static string $resource = DeliveryServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
