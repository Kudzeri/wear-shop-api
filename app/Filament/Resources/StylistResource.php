<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StylistResource\Pages;
use App\Models\Stylist;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Resources\Resource;

class StylistResource extends Resource
{
    protected static ?string $model = Stylist::class;
    protected static ?string $navigationLabel = 'Выбор стилиста';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                FileUpload::make('image_url')
                    ->image()
                    ->label('Фото выбора стилиста')
                    ->required(),

                Select::make('products')
                    ->label('Товары')
                    ->relationship('products', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Фото стилиста'),

                Tables\Columns\TextColumn::make('products.name')
                    ->label('Товары')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStylists::route('/'),
            'create' => Pages\CreateStylist::route('/create'),
            'edit' => Pages\EditStylist::route('/{record}/edit'),
        ];
    }
}

