<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoriesResource\Pages;
use App\Filament\Resources\StoriesResource\RelationManagers;
use App\Models\Stories;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;

class StoriesResource extends Resource
{
    protected static ?string $model = Stories::class;
    protected static ?string $navigationLabel = 'Сторисы';
    protected static ?string $pluralModelLabel = 'Сторисы';
    protected static ?string $modelLabel = 'Сторис';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                        ->label('Заголовок')
                        ->required(),
                FileUpload::make('image_url')
                    ->image()
                    ->label('Изображение')
                    ->dehydrateStateUsing(fn ($state) => $state ? "https://siveno.shop/" . $state : $state) // Исправлено
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
                    ->label('Изображение'),

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
            'index' => Pages\ListStories::route('/'),
            'create' => Pages\CreateStories::route('/create'),
            'edit' => Pages\EditStories::route('/{record}/edit'),
        ];
    }
}
