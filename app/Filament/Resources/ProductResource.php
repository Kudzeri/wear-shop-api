<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
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
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Storage;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'Товар';
    protected static ?string $navigationLabel = 'Товары';
    protected static ?string $modelLabel = 'Товар';
    protected static ?string $pluralModelLabel = 'Товары';

    public static function form(Forms\Form $form): Forms\Form
{
    return $form
        ->schema([
            TextInput::make('name')
                ->label('Название товара')
                ->required(),

            Select::make('category_id')
                ->label('Категория')
                ->options(Category::query()->whereNotNull('title')->pluck('title', 'id')->toArray())
                ->searchable()
                ->required(),

            Textarea::make('description')
                ->label('Описание товара')
                ->required(),

            FileUpload::make('video_file')
                ->label('Загрузить видео (10мб)')
                ->disk('public')
                ->directory('products/videos')
                ->acceptedFileTypes(['video/mp4', 'video/mov', 'video/avi'])
                ->maxSize(10240)
                ->nullable()
                ->dehydrated(fn ($state) => filled($state)),

            Textarea::make('composition_care')
                ->label('Состав и уход')
                ->nullable(),

            CheckboxList::make('colors')
                ->label('Цвета')
                ->relationship('colors', 'name'),

            CheckboxList::make('sizes')
                ->label('Размеры')
                ->relationship('sizes', 'name'),

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
                TextColumn::make('name')
                    ->label('Название товара')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.title')
                    ->label('Категория')
                    ->sortable()
                    ->searchable(), // Добавлен поиск по категории

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->date()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Цена')
                    ->sortable()
                    ->money('К'),

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
