<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailTemplateResource\Pages;
use App\Filament\Resources\MailTemplateResource\RelationManagers;
use App\Mail\MassTemplateMail;
use App\Models\MailTemplate;
use App\Models\Subscriber;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

class MailTemplateResource extends Resource
{
    protected static ?string $model = MailTemplate::class;
    protected static ?string $navigationGroup = 'Рассылка';
    protected static ?string $navigationLabel = 'Письма';
    protected static ?string $modelLabel = 'Письмо';
    protected static ?string $pluralModelLabel = 'Письма';

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Название письма (для администратора)')
                    ->required(),
                Forms\Components\TextInput::make('subject')->label('Заголовок письма')
                    ->required(),
                Forms\Components\RichEditor::make('content')->label('Тело письма')
                    ->required()
                    ->helperText('Используй [name] для вставки имени подписчика.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->label('Название письма'),
            Tables\Columns\TextColumn::make('subject')->label('Заголовок'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Дата создания'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Action::make('send')
                ->label('Отправить всем')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (MailTemplate $record) {
                    $subscribers = Subscriber::all();
                    foreach ($subscribers as $subscriber) {
                        Mail::to($subscriber->email)
                            ->send(new MassTemplateMail($record, $subscriber));
                    }
                })
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
            'index' => Pages\ListMailTemplates::route('/'),
            'create' => Pages\CreateMailTemplate::route('/create'),
            'edit' => Pages\EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
