<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyPointResource\Pages;
use App\Models\LoyaltyPoint;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoyaltyResource extends Resource
{
    protected static ?string $model = LoyaltyPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-percent-badge';

    protected static ?string $navigationLabel = 'Программа лояльности';

    protected static ?string $modelLabel = 'Баллы лояльности';
    protected static ?string $navigationGroup = 'Маркетинг';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Пользователь')
                    ->relationship('user', 'name', function ($query) {
                        return $query->selectRaw("id, CONCAT(name, ' ', surname) as name");
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('points')
                    ->label('Баллы')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->label('Причина')
                    ->nullable(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->sortable(),
                Tables\Columns\TextColumn::make('points')
                    ->label('Баллы')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Причина')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата начисления')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Дата')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('С'),
                        Forms\Components\DatePicker::make('until')->label('По'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date))),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => LoyaltyResource\Pages\ListLoyalties::route('/'),
            'create' => LoyaltyResource\Pages\CreateLoyalty::route('/create'),
            'edit' => LoyaltyResource\Pages\EditLoyalty::route('/{record}/edit'),
        ];
    }
}
