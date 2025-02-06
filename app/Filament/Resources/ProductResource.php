<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'Товар';
    protected static ?string $navigationLabel = 'Товары';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Название товара')
                    ->required(),

                TextInput::make('category_id')
                    ->label('Категория')
                    ->required()
                    ->integer(),

                Textarea::make('description')
                    ->label('Описание товара')
                    ->required(),

                FileUpload::make('image_files')
                    ->label('Изображения')
                    ->multiple()
                    ->disk('public')
                    ->directory('products')
                    ->previewable() // Добавляет предпросмотр изображений
                    ->reorderable(), // Позволяет менять порядок изображений

                TextInput::make('video_url')
                    ->label('Ссылка на видео')
                    ->url()
                    ->nullable(),

                Textarea::make('composition_care')
                    ->label('Состав и уход')
                    ->nullable(),

                CheckboxList::make('colors')
                    ->label('Цвета')
                    ->relationship('colors', 'name')
                    ->options(Color::all()->pluck('name', 'id')->toArray()),

                CheckboxList::make('sizes')
                    ->label('Размеры')
                    ->relationship('sizes', 'name')
                    ->options(Size::all()->pluck('name', 'id')->toArray()),

                Forms\Components\KeyValue::make('preference')
                    ->label('Обмеры')
                    ->nullable(),

                Forms\Components\KeyValue::make('measurements')
                    ->label('Параметры модели')
                    ->nullable(),

                TextInput::make('price')
                    ->label('Цена')
                    ->required()
                    ->numeric()
                    ->prefix('руб.')
                    ->minValue(0),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_files')
                    ->label('Изображение')
                    ->getStateUsing(fn ($record) => is_array($record->image_files) && count($record->image_files) > 0 ? $record->image_files[0] : null)
                    ->disk('public')
                    ->square()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Название товара')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable(),

                TagsColumn::make('colors.name')
                    ->label('Цвета')
                    ->sortable(),

                TagsColumn::make('sizes.name')
                    ->label('Размеры')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->date()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->sortable()
                    ->money('руб'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
