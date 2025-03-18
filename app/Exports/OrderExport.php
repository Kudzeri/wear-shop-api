<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;

class OrderExport implements FromCollection
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection(): Collection
    {
        $query = Order::query();
        foreach ($this->filters as $key => $value) {
            $query->where($key, $value);
        }
        $orders = $query->get([
            'id_order', 'date_order', 'number_order_1c', 'date_order_1c',
            'id_customer', 'id_customer_1c', 'id_promo', 'id_promo_1c',
            'status_payment', 'check_number', 'check_sum', 'check_nds',
            'check_date', 'id_delivery_service', 'id_delivery_service_1c',
            'delivery_type', 'address', 'id_pick_up_point', 'id_pick_up_point_1c',
            'delivery_number', 'order_amount'
        ]);
        // Преобразуем даты в нужный формат
        return $orders->map(function($order) {
            $order->date_order = $order->date_order ? Carbon::parse($order->date_order)->format('Y-m-d H:i:s') : null;
            $order->date_order_1c = $order->date_order_1c ? Carbon::parse($order->date_order_1c)->format('Y-m-d H:i:s') : null;
            return $order;
        });
    }
}
