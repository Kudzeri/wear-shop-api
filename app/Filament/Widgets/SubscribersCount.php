<?php

namespace App\Filament\Widgets;

use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SubscribersCount extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Подписчиков', Subscriber::count())
                ->description('Всего человек подписались на рассылку')
                ->icon('heroicon-o-envelope')
                ->color('success'),
        ];
    }
}
