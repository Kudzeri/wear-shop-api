<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Filament\Resources\AddressResource\RelationManagers\UsersRelationManager;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;
    protected static ?string $navigationLabel = 'Адреса';
    protected static ?string $navigationGroup = 'Пользователь';
    protected static ?string $navigationIcon = 'heroicon-o-map';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_primary')
                    ->label('Основной адрес')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('state')
                    ->label('Штат/Область')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('city')
                    ->label('Город')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('street')
                    ->label('Улица')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('house')
                    ->label('Дом')
                    ->required()
                    ->maxLength(10),

                Forms\Components\TextInput::make('apartment')
                    ->label('Квартира')
                    ->nullable()
                    ->maxLength(10),

                Forms\Components\TextInput::make('postal_code')
                    ->label('Почтовый индекс')
                    ->nullable()
                    ->maxLength(20),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_address')
                    ->label('Полный адрес')
                    ->wrap()
                    ->searchable(['city', 'street', 'house', 'apartment']),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Основной')
                    ->boolean(),

                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Индекс')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Фильтры можно добавить при необходимости
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
