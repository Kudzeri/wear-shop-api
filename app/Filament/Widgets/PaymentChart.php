<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\BarChartWidget;

class PaymentChart extends BarChartWidget
{
    protected static ?string $heading = 'Доходы за последние 7 дней';

    protected function getData(): array
    {
        // Получаем даты за последние 7 дней
        $dates = collect(range(6, 0))->map(fn ($i) => Carbon::now()->subDays($i)->format('Y-m-d'));

        // Группируем платежи по дате и суммируем успешные
        $payments = Payment::where('status', 'succeeded')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(6))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Формируем массив данных
        $earnings = $dates->map(fn ($date) => $payments[$date] ?? 0);

        return [
            'datasets' => [
                [
                    'label' => 'Доход (₽)',
                    'data' => $earnings->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }
}
