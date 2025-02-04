<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Models\Order;
use Illuminate\Support\Carbon;

class OrdersChart extends LineChartWidget
{
    protected static ?string $heading = 'Заказы за последний месяц';

    protected function getData(): array
    {
        $dates = collect();
        $orderCounts = collect();

        // Генерируем последние 30 дней
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates->push($date);
            $orderCounts->push(Order::whereDate('created_at', $date)->count());
        }

        return [
            'datasets' => [
                [
                    'label' => 'Количество заказов',
                    'data' => $orderCounts->toArray(),
                    'borderColor' => '#3b82f6', // Синий цвет линии
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)', // Полупрозрачный фон
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }
}
