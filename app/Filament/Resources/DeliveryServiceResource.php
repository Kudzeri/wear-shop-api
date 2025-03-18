<?php

namespace App\Filament\Resources;

use App\Models\DeliveryService;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;

class DeliveryServiceResource extends Resource
{
    protected static ?string $model = DeliveryService::class;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // ...existing form schema...
                Forms\Components\TextInput::make('id_delivery_service')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('id_delivery_service_1c')
                    ->required(),
                Forms\Components\TextInput::make('delivery_service')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                // ...existing columns...
                Tables\Columns\TextColumn::make('id_delivery_service'),
                Tables\Columns\TextColumn::make('id_delivery_service_1c'),
                Tables\Columns\TextColumn::make('delivery_service'),
            ]);
    }

    // Добавление pages для ресурса
    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\DeliveryServiceResource\Pages\ListDeliveryServices::route('/'),
            'create' => \App\Filament\Resources\DeliveryServiceResource\Pages\CreateDeliveryService::route('/create'),
            'edit'   => \App\Filament\Resources\DeliveryServiceResource\Pages\EditDeliveryService::route('/{record}/edit'),
        ];
    }
}
