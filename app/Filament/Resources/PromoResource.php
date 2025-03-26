<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoResource\Pages;
use App\Models\Promo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromoResource extends Resource
{
    protected static ?string $model = Promo::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Промокоды';
    protected static ?string $modelLabel = 'Промокод';
    protected static ?string $pluralModelLabel = 'Промокоды';
    protected static ?string $navigationGroup = 'Маркетинг';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Код')
                ->required(),
            Forms\Components\TextInput::make('discount')
                ->label('Скидка (%)')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->required(),
            Forms\Components\DatePicker::make('expires_at')
                ->label('Действует до')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->label('Код')->searchable(),
            Tables\Columns\TextColumn::make('discount')->label('Скидка')->sortable(),
            Tables\Columns\TextColumn::make('expires_at')->label('Действует до')->date(),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromos::route('/'),
            'create' => Pages\CreatePromo::route('/create'),
            'edit' => Pages\EditPromo::route('/{record}/edit'),
        ];
    }
}
