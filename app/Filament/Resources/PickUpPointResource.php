<?php

namespace App\Filament\Resources;

use App\Models\PickUpPoint;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;

class PickUpPointResource extends Resource
{
    protected static ?string $model = PickUpPoint::class;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // ...existing form schema...
                Forms\Components\TextInput::make('id_pickup_point')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('id_pickup_point_1c')
                    ->required(),
                Forms\Components\TextInput::make('pick_up_point')
                    ->required(),
                Forms\Components\TextInput::make('id_delivery_service')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('id_delivery_service_1c')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                // ...existing columns...
                Tables\Columns\TextColumn::make('id_pickup_point'),
                Tables\Columns\TextColumn::make('id_pickup_point_1c'),
                Tables\Columns\TextColumn::make('pick_up_point'),
                Tables\Columns\TextColumn::make('id_delivery_service'),
                Tables\Columns\TextColumn::make('id_delivery_service_1c'),
            ]);
    }

    // Добавление pages для ресурса
    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\PickUpPointResource\Pages\ListPickUpPoints::route('/'),
            'create' => \App\Filament\Resources\PickUpPointResource\Pages\CreatePickUpPoint::route('/create'),
            'edit'   => \App\Filament\Resources\PickUpPointResource\Pages\EditPickUpPoint::route('/{record}/edit'),
        ];
    }
}
