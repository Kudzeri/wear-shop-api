<?php

namespace App\Filament\Resources;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage; // added import

class ProductResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(fn ($record) => Storage::url($record->image)), // updated image URL generation
            ]);
    }
}