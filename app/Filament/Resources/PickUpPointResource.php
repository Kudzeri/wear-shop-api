<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PickUpPointResource\Pages;
use App\Filament\Resources\PickUpPointResource\RelationManagers;
use App\Models\PickUpPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PickUpPointResource extends Resource
{
    protected static ?string $model = PickUpPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
    return $form
            ->schema([
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
                Tables\Columns\TextColumn::make('id_pickup_point'),
                Tables\Columns\TextColumn::make('id_pickup_point_1c'),
                Tables\Columns\TextColumn::make('pick_up_point'),
                Tables\Columns\TextColumn::make('id_delivery_service'),
                Tables\Columns\TextColumn::make('id_delivery_service_1c'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPickUpPoints::route('/'),
            'create' => Pages\CreatePickUpPoint::route('/create'),
            'edit' => Pages\EditPickUpPoint::route('/{record}/edit'),
        ];
    }
}
