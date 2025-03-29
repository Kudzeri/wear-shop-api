<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);

        $record->assignRole('admin');
        $record->givePermissionTo('view_filament');

        return $record;
    }

}
