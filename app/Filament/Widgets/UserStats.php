<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Всего пользователей', User::count())
                ->description('Общее число зарегистрированных пользователей')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Card::make('Регистраций за 30 дней', User::where('created_at', '>=', Carbon::now()->subDays(30))->count())
                ->description('Новые пользователи за последний месяц')
                ->icon('heroicon-o-user-plus')
                ->color('success'),
        ];
    }
}
