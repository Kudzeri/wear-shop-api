<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductImageResource\Pages;
use App\Filament\Resources\ProductImageResource\RelationManagers;
use App\Models\ProductImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductImageResource extends Resource
{
    protected static ?string $model = ProductImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable(),
                \Filament\Forms\Components\Radio::make('upload_type')
                    ->options([
                        'file' => 'Upload File',
                        'link' => 'Image URL',
                    ])
                    ->default('file')
                    ->reactive(),
                \Filament\Forms\Components\FileUpload::make('image_path')
                    ->disk('public')
                    ->directory('product_images')
                    ->image()
                    ->visible(fn ($get) => $get('upload_type') === 'file'),
                \Filament\Forms\Components\TextInput::make('image_path')
                    ->label('Image URL')
                    ->url()
                    ->visible(fn ($get) => $get('upload_type') === 'link'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('image_path'),
                \Filament\Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductImages::route('/'),
            'create' => Pages\CreateProductImage::route('/create'),
            'edit' => Pages\EditProductImage::route('/{record}/edit'),
        ];
    }
}
