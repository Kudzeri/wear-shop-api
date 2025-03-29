<?php

namespace App\Exports;

use App\Models\Order;

class OrderExport
{
    public function getData(): array
    {
        $orders = Order::with('customer')->get();
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                $order->id,
                $order->customer->name ?? '',
                $order->total_price,
                $order->created_at->format('d.m.Y'),
            ];
        }
        return $data;
    }

    public function getHeadings(): array
    {
        return ['ID', 'Клиент', 'Сумма', 'Дата'];
    }
}
