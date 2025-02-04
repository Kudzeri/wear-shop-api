<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Filament\Resources\AddressResource\RelationManagers\UsersRelationManager;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\QueryBuilder;
class AddressResource extends Resource
{
    protected static ?string $model = Address::class;
    protected static ?string $navigationLabel = 'Адреса';
    protected static ?string $navigationGroup = 'Пользователь';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_primary')
                    ->label('Основной адрес')
                    ->nullable(),

                Forms\Components\TextInput::make('state')
                    ->label('Штат/Область')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('city')
                    ->label('Город')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('postal_code')
                    ->label('Почтовый код')
                    ->nullable()
                    ->maxLength(20),

                Forms\Components\TextInput::make('apartment')
                    ->label('Квартира/Дом')
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('state')->label('Штат/Область'),
                Tables\Columns\TextColumn::make('city')->label('Город'),
                Tables\Columns\TextColumn::make('postal_code')->label('Почтовый код'),
                Tables\Columns\TextColumn::make('apartment')->label('Квартира/Дом'),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(), // Создание нового адреса
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Редактирование адреса
                Tables\Actions\DeleteAction::make(), // Удаление адреса
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Удаление нескольких адресов
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
