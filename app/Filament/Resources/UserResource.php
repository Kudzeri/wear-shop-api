<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\AddressRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Пользователь';
    protected static ?string $navigationLabel = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('surname')
                    ->label('Фамилия')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Электронная почта')
                    ->required()
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->nullable()
                    ->maxLength(20),

                Forms\Components\FileUpload::make('avatar_url')
                    ->label('Аватар')
                    ->image()
                    ->dehydratedStateUsing(fn ($state) => $state ? "https://siveno.shop/" . $state : $state) // Изменено
                    ->nullable(),

                Forms\Components\Select::make('role')
                    ->label('Роль')
                    ->options([
                        'user' => 'Пользователь',
                        'admin' => 'Администратор',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->required(fn ($get) => !$get('id'))
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrated(fn ($state) => filled($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Имя')->limit(50),
                TextColumn::make('surname')->label('Фамилия')->limit(50),
                ImageColumn::make('avatar_url')->label('Аватар'),
                TextColumn::make('email')->label('Электронная почта')->limit(50),
                TextColumn::make('phone')->label('Телефон')->limit(50),
                TextColumn::make('role')->label('Роль')->limit(50),
                TextColumn::make('created_at')->label('Дата создания')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Фильтр по роли')
                    ->options([
                        'user' => 'Пользователь',
                        'admin' => 'Администратор',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
