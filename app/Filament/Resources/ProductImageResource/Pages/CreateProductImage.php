<?php

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Resources\ProductImageResource;
use App\Models\ProductImage;
use Filament\Resources\Pages\CreateRecord;

class CreateProductImage extends CreateRecord
{
    protected static string $resource = ProductImageResource::class;

    protected function handleRecordCreation(array $data): ProductImage
    {
        foreach ($data['images'] as $imagePath) {
            ProductImage::create([
                'product_id' => $data['product_id'],
                'image_path' => $imagePath,
            ]);
        }

        $this->notify('success', 'Изображения успешно загружены');

        // Возвращаем фиктивную запись, чтобы Filament не упал
        return new ProductImage();
    }
}
