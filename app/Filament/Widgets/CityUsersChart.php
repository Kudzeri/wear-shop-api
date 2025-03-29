<?php

namespace App\Filament\Widgets;

use Filament\Widgets\PieChartWidget;
use App\Models\Address;
use Illuminate\Database\Eloquent\Builder;

class CityUsersChart extends PieChartWidget
{
    protected static ?string $heading = 'Распределение пользователей по городам';

    protected function getData(): array
    {
        // Получаем топ-10 городов с наибольшим числом пользователей
        $cities = Address::withCount(['users as user_count'])
            ->orderByDesc('user_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $cities->pluck('user_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1',
                        '#14b8a6', '#f43f5e', '#8b5cf6', '#ec4899', '#22c55e'
                    ],
                ],
            ],
            'labels' => $cities->pluck('city')->toArray(),
        ];
    }
}
