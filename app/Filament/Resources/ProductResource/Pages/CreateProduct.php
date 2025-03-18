<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Product
    {
        $record = Product::create($data);
        if (isset($data['images'])) {
            $record->syncImagesAdm($data['images']);
        }
        return $record;
    }
}
