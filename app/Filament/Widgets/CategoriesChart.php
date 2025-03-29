<?php

namespace App\Filament\Widgets;

use Filament\Widgets\PieChartWidget;
use App\Models\Category;
use App\Models\Product;

class CategoriesChart extends PieChartWidget
{
    protected static ?string $heading = 'Товары по категориям';

    protected function getData(): array
    {
        $categories = Category::withCount('products')->get();

        return [
            'datasets' => [
                [
                    'data' => $categories->pluck('products_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1',
                        '#14b8a6', '#f43f5e', '#8b5cf6', '#ec4899', '#22c55e'
                    ],
                ],
            ],
            'labels' => $categories->pluck('title')->toArray(),
        ];
    }
}
