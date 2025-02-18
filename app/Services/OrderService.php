<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class OrderService
 *
 * Сервис для создания и обновления заказов с интеграцией платежей через YooKassa
 * и применения скидок через LoyaltyService.
 */
class OrderService
{
    /**
     * @var \App\Services\YooKassaService
     */
    public YooKassaService $yooKassaService;

    /**
     * @var \App\Services\LoyaltyService
     */
    public LoyaltyService $loyaltyService;

    /**
     * OrderService constructor.
     *
     * @param YooKassaService $yooKassaService
     * @param LoyaltyService  $loyaltyService
     */
    public function __construct(YooKassaService $yooKassaService, LoyaltyService $loyaltyService)
    {
        $this->yooKassaService = $yooKassaService;
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Создание заказа для пользователя.
     *
     * Выполняет следующие шаги:
     * - Получает список товаров по переданным ID.
     * - Вычисляет общую стоимость заказа.
     * - Применяет скидку, если используется лояльность.
     * - Создает заказ и связанные с ним позиции заказа.
     * - Инициализирует платеж через YooKassa.
     *
     * @param array $data Массив данных заказа, например:
     *                    [
     *                      'address_id' => 1,
     *                      'items' => [
     *                          ['product_id' => 10, 'size_id' => 2, 'quantity' => 3],
     *                          // ...
     *                      ],
     *                      'delivery' => 'express',
     *                      'use_loyalty_points' => true,
     *                      // и т.д.
     *                    ]
     * @param mixed $user Текущий пользователь (объект пользователя)
     *
     * @return array Массив с созданными объектами заказа, платежа и информацией о скидке, например:
     *               [
     *                  'order' => (Order),
     *                  'payment' => (Payment),
     *                  'discount_applied' => ['final_amount' => 1500.75]
     *               ]
     *
     * @throws Exception Если список товаров пуст, продукт не найден или итоговая сумма заказа ≤ 0.
     */
    public function createOrder(array $data, $user): array
    {
        // Получаем все ID продуктов одним запросом
        $productIds = array_column($data['items'], 'product_id');
        if (empty($productIds)) {
            throw new Exception('Список товаров пуст');
        }
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Вычисляем общую стоимость заказа
        $totalPrice = 0;
        foreach ($data['items'] as $item) {
            if (!isset($products[$item['product_id']])) {
                throw new Exception("Продукт с ID {$item['product_id']} не найден");
            }
            $totalPrice += $products[$item['product_id']]->price * $item['quantity'];
        }

        // Применяем скидку, если используется лояльность
        if (!empty($data['use_loyalty_points'])) {
            $discountData = $this->loyaltyService->applyDiscount($user, $totalPrice);
        } else {
            $discountData = ['final_amount' => $totalPrice];
        }

        if ($discountData['final_amount'] <= 0) {
            throw new Exception('Сумма заказа после скидки не может быть 0');
        }

        return DB::transaction(function () use ($data, $user, $discountData, $products) {
            // Создаем заказ
            $order = Order::create([
                'user_id'     => $user->id,
                'address_id'  => $data['address_id'],
                'total_price' => $discountData['final_amount'],
                'status'      => 'pending',
                'delivery'    => $data['delivery'],
            ]);

            // Готовим данные для массовой вставки позиций заказа
            $orderItems = [];
            foreach ($data['items'] as $item) {
                $orderItems[] = [
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'size_id'    => $item['size_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $products[$item['product_id']]->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            OrderItem::insert($orderItems);

            // Инициализируем платеж через YooKassa
            try {
                $yooPayment = $this->yooKassaService->initiatePayment($order, $discountData['final_amount']);
                if (!isset($yooPayment['transaction_id'])) {
                    throw new Exception("Ответ от YooKassa не содержит transaction_id");
                }
                $transactionId = $yooPayment['transaction_id'];
            } catch (Exception $ex) {
                Log::error('Ошибка при инициализации платежа через YooKassa', ['error' => $ex->getMessage()]);
                throw new Exception('Ошибка при инициализации платежа через YooKassa: ' . $ex->getMessage());
            }

            $payment = Payment::create([
                'user_id'        => $user->id,
                'order_id'       => $order->id,
                'amount'         => $discountData['final_amount'],
                'currency'       => 'RUB',
                'status'         => 'pending',
                'payment_method' => 'yookassa',
                'transaction_id' => $transactionId,
            ]);

            Log::info('Создан заказ', ['order_id' => $order->id, 'user_id' => $user->id]);
            Log::info('Создан платеж', [
                'payment_id' => $payment->id,
                'amount'     => $discountData['final_amount'],
                'method'     => 'yookassa'
            ]);

            return [
                'order'            => $order,
                'payment'          => $payment,
                'discount_applied' => $discountData,
            ];
        });
    }

    /**
     * Создание заказа для администратора (без инициализации платежа и скидок).
     *
     * @param array $data Массив данных заказа для администратора, например:
     *                    [
     *                      'user_id' => 2,
     *                      'address_id' => 5,
     *                      'delivery' => 'standard',
     *                      'items' => [
     *                          ['product_id' => 10, 'size_id' => 2, 'quantity' => 3],
     *                          // ...
     *                      ]
     *                    ]
     *
     * @return Order Созданный заказ.
     *
     * @throws Exception Если список товаров пуст или продукт не найден.
     */
    public function createAdminOrder(array $data): Order
    {
        $productIds = array_column($data['items'], 'product_id');
        if (empty($productIds)) {
            throw new Exception('Список товаров пуст');
        }
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $totalPrice = 0;
        foreach ($data['items'] as $item) {
            if (!isset($products[$item['product_id']])) {
                throw new Exception("Продукт с ID {$item['product_id']} не найден");
            }
            $totalPrice += $products[$item['product_id']]->price * $item['quantity'];
        }

        return DB::transaction(function () use ($data, $totalPrice, $products) {
            $order = Order::create([
                'user_id'     => $data['user_id'],
                'address_id'  => $data['address_id'],
                'total_price' => $totalPrice,
                'status'      => 'pending',
                'delivery'    => $data['delivery'] ?? null,
            ]);

            $orderItems = [];
            foreach ($data['items'] as $item) {
                $orderItems[] = [
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'size_id'    => $item['size_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $products[$item['product_id']]->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            OrderItem::insert($orderItems);

            return $order;
        });
    }

    /**
     * Обновление заказа (например, для администратора).
     *
     * Обновляет общую стоимость и позиции заказа. Если позиция товара уже существует,
     * обновляет количество и цену, иначе создаёт новую позицию.
     *
     * @param Order $order Заказ, который необходимо обновить.
     * @param array $data  Данные для обновления заказа, например:
     *                     [
     *                       'address_id' => 5,
     *                       'items' => [
     *                           ['product_id' => 10, 'size_id' => 2, 'quantity' => 3],
     *                           // ...
     *                       ]
     *                     ]
     *
     * @return Order Обновлённый заказ.
     *
     * @throws Exception Если список товаров пуст или продукт не найден.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        $productIds = array_column($data['items'], 'product_id');
        if (empty($productIds)) {
            throw new Exception('Список товаров пуст');
        }
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $totalPrice = 0;
        foreach ($data['items'] as $item) {
            if (!isset($products[$item['product_id']])) {
                throw new Exception("Продукт с ID {$item['product_id']} не найден");
            }
            $totalPrice += $products[$item['product_id']]->price * $item['quantity'];
        }

        return DB::transaction(function () use ($order, $data, $totalPrice, $products) {
            $order->update([
                'address_id'  => $data['address_id'],
                'total_price' => $totalPrice,
            ]);

            // Обновляем или создаем позиции заказа
            foreach ($data['items'] as $item) {
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($orderItem) {
                    $orderItem->update([
                        'quantity' => $item['quantity'],
                        'price'    => $products[$item['product_id']]->price,
                        'size_id'  => $item['size_id'],
                    ]);
                } else {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'size_id'    => $item['size_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $products[$item['product_id']]->price,
                    ]);
                }
            }

            return $order;
        });
    }
}
