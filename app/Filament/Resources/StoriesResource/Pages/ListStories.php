<?php

namespace App\Filament\Resources\StoriesResource\Pages;

use App\Filament\Resources\StoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStories extends ListRecords
{
    protected static string $resource = StoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
