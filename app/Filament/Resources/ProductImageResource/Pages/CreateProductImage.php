<?php

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Resources\ProductImageResource;
use App\Models\ProductImage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductImage extends CreateRecord
{
    protected static string $resource = ProductImageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [];
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        foreach ($data['images'] as $imagePath) {
            ProductImage::create([
                'product_id' => $data['product_id'],
                'image_path' => $imagePath,
            ]);
        }

        // Опционально — показать уведомление
        $this->notify('success', 'Изображения успешно загружены');
    }
}
