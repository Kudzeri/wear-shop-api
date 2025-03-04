<?php

namespace App\Filament\Resources\StoriesResource\Pages;

use App\Filament\Resources\StoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStories extends EditRecord
{
    protected static string $resource = StoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
