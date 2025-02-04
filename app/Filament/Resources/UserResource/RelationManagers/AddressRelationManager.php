<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('street')
                    ->label('Улица')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('city')
                    ->label('Город')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('zipcode')
                    ->label('Почтовый индекс')
                    ->nullable()
                    ->maxLength(20),

                Forms\Components\Toggle::make('is_primary')
                    ->label('Основной адрес')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street')
            ->columns([
                Tables\Columns\TextColumn::make('street')->label('Улица'),
                Tables\Columns\TextColumn::make('city')->label('Город'),
                Tables\Columns\TextColumn::make('zipcode')->label('Почтовый индекс'),
                Tables\Columns\BooleanColumn::make('is_primary')->label('Основной'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_primary')
                    ->label('Основной адрес')
                    ->options([
                        true => 'Да',
                        false => 'Нет',
                    ]),
                Tables\Filters\SelectFilter::make('city')
                    ->label('Город'),
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
}
