<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryServiceResource\Pages;
use App\Filament\Resources\DeliveryServiceResource\RelationManagers;
use App\Models\DeliveryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryServiceResource extends Resource
{
    protected static ?string $model = DeliveryService::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $modelLabel = 'Служба доставки';
    protected static ?string $pluralModelLabel = 'Службы доставки';
    protected static ?string $navigationGroup = 'Сайт';
    protected static ?string $navigationLabel = 'Службы доставки';


    public static function form(Form $form): Form
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryServices::route('/'),
            'create' => Pages\CreateDeliveryService::route('/create'),
            'edit' => Pages\EditDeliveryService::route('/{record}/edit'),
        ];
    }
}
