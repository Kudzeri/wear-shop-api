<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // Обновлённая логика создания товара
    protected function handleRecordCreation(array $data): Product
    {
        $record = Product::create($data);
        if (isset($data['images'])) {
            $record->syncImages($data['images']);
        }
        return $record;
    }
}
