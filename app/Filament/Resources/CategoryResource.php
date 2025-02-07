<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TagsColumn;
use Filament\Forms\Components\Toggle;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Товар';
    protected static ?string $navigationLabel = 'Категории';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(Category::class, 'slug', ignoreRecord: true),

                TextInput::make('title')
                    ->label('Название категории')
                    ->required(),

                Select::make('parent_id')
                    ->label('Родительская категория')
                    ->relationship('parent', 'title')
                    ->nullable()
                    ->searchable(),

                FileUpload::make('image')
                    ->label('Изображение')
                    ->disk('public')
                    ->directory('categories')
                    ->previewable()
                    ->reorderable()
                    ->getUploadedFileNameForStorageUsing(fn ($file) => 'categories/' . $file->hashName())  // Корректное сохранение пути
                    ->dehydrated(false) // Не очищает значение, если ничего не загружено
                    ->nullable(),

                Toggle::make('is_sale')
                    ->label('Распродажа')
                    ->default(false)
                    ->dehydrated()
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Изображение')
                    ->disk('public')
                    ->size(50), // Укажи размер превью

                TextColumn::make('title')
                    ->label('Название категории')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->sortable(),

                TextColumn::make('parent.title')
                    ->label('Родительская категория')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_sale')
                    ->label('Распродажа')
                    ->sortable()
                    ->boolean()
            ])
            ->filters([])
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
