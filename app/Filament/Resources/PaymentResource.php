<?php

namespace App\Filament\Resources;

use App\Models\Payment;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\{Select, TextInput, Section};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\PaymentResource\Pages\{ListPayments, CreatePayment, EditPayment};

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Платежи';
    protected static ?string $navigationGroup = 'Пользователь';
    protected static ?string $pluralModelLabel = 'Платежи';
    protected static ?string $modelLabel = 'Платеж';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Информация о платеже')
                ->schema([
                    Select::make('user_id')
                        ->label('Пользователь')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('order_id')
                        ->label('Заказ')
                        ->relationship('order', 'id')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('amount')
                        ->label('Сумма')
                        ->numeric()
                        ->required(),

                    TextInput::make('currency')
                        ->label('Валюта')
                        ->default('RUB')
                        ->disabled(),

                    Select::make('status')
                        ->label('Статус')
                        ->options([
                            'pending' => 'Ожидание',
                            'succeeded' => 'Успешно',
                            'failed' => 'Ошибка',
                            'cancelled' => 'Отменен',
                        ])
                        ->required(),

                    TextInput::make('payment_method')
                        ->label('Метод оплаты')
                        ->nullable(),

                    TextInput::make('transaction_id')
                        ->label('ID транзакции')
                        ->nullable(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.name')->label('Пользователь')->sortable(),
                TextColumn::make('order.id')->label('Заказ')->sortable(),
                TextColumn::make('amount')->label('Сумма')->sortable(),
                TextColumn::make('currency')->label('Валюта'),
                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'succeeded',
                        'danger' => 'failed',
                        'gray' => 'cancelled',
                    ])
                    ->sortable(),
                TextColumn::make('payment_method')->label('Метод оплаты'),
                TextColumn::make('transaction_id')->label('ID транзакции')->copyable(),
                TextColumn::make('created_at')->label('Создан')->dateTime(),
                TextColumn::make('updated_at')->label('Обновлен')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидание',
                        'succeeded' => 'Успешно',
                        'failed' => 'Ошибка',
                        'cancelled' => 'Отменен',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}
