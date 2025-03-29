<?php

namespace App\Filament\Resources\StylistResource\Pages;

use App\Filament\Resources\StylistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStylist extends EditRecord
{
    protected static string $resource = StylistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
