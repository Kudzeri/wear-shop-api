<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Операции с заказами"
 * )
 */

/**
 * @OA\Components(
 *     @OA\Schema(
 *         schema="Order",
 *         type="object",
 *         required={"id", "user_id", "address_id", "total_price", "status"},
 *         @OA\Property(property="id", type="integer", description="ID заказа"),
 *         @OA\Property(property="user_id", type="integer", description="ID пользователя"),
 *         @OA\Property(property="address_id", type="integer", description="ID адреса доставки"),
 *         @OA\Property(property="total_price", type="number", format="float", description="Общая сумма заказа"),
 *         @OA\Property(property="status", type="string", enum={"pending", "processed", "shipped", "delivered", "cancelled"}, description="Статус заказа"),
 *         @OA\Property(
 *             property="items",
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/OrderItem")
 *         ),
 *         @OA\Property(
 *             property="address",
 *             type="object",
 *             ref="#/components/schemas/Address"
 *         )
 *     ),
 *
 *     @OA\Schema(
 *         schema="OrderItem",
 *         type="object",
 *         required={"product_id", "size_id", "quantity", "price"},
 *         @OA\Property(property="product_id", type="integer", description="ID продукта"),
 *         @OA\Property(property="size_id", type="integer", description="ID размера продукта"),
 *         @OA\Property(property="quantity", type="integer", description="Количество товара"),
 *         @OA\Property(property="price", type="number", format="float", description="Цена товара")
 *     ),
 *
 *     @OA\Schema(
 *         schema="Address",
 *         type="object",
 *         required={"id", "street", "city", "postal_code", "country"},
 *         @OA\Property(property="id", type="integer", description="ID адреса"),
 *         @OA\Property(property="street", type="string", description="Улица"),
 *         @OA\Property(property="city", type="string", description="Город"),
 *         @OA\Property(property="postal_code", type="string", description="Почтовый код"),
 *         @OA\Property(property="country", type="string", description="Страна")
 *     )
 * )
 */

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Создать заказ",
     *     description="Позволяет авторизованному пользователю создать новый заказ.",
     *     operationId="createOrder",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id", "items", "delivery"},
     *             @OA\Property(property="address_id", type="integer", description="ID адреса доставки"),
     *             @OA\Property(property="delivery", type="string", description="Способ доставки"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", description="ID продукта"),
     *                     @OA\Property(property="size_id", type="integer", description="ID размера продукта"),
     *                     @OA\Property(property="quantity", type="integer", description="Количество товара")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Создан заказ",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизован"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации данных"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Проверка, что пользователь авторизован
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery' => 'string|required'
        ]);

        // Вычисляем общую цену
        $totalPrice = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $size = Size::find($item['size_id']);
            $price = $product->price; // Цена товара
            $totalPrice += $price * $item['quantity']; // Добавляем стоимость в общую сумму
        }

        // Создаем заказ
        $order = Order::create([
            'user_id' => $user->id,  // Присваиваем текущего пользователя
            'address_id' => $validated['address_id'],
            'total_price' => $totalPrice,
            'status' => 'pending', // Начальный статус
            'delivery' => $validated['delivery'],
        ]);

        // Создаем позиции заказа
        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'size_id' => $item['size_id'],
                'quantity' => $item['quantity'],
                'price' => Product::find($item['product_id'])->price,
            ]);
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => $totalPrice,
            'currency' => 'RUB',
            'status' => 'pending',
            'payment_method' => null,
            'transaction_id' => null
        ]);

        return response()->json([
            'order' => $order,
            'payment' => $payment
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/orders",
     *     summary="Получить все заказы текущего пользователя",
     *     description="Позволяет авторизованному пользователю получить все свои заказы с деталями (продукты, размеры, адрес).",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов успешно получен",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Не авторизован")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказы не найдены",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Заказы не найдены")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        // Получаем все заказы пользователя
        $orders = Order::where('user_id', $user->id)
            ->with('items.product', 'items.size', 'address') // Подключаем связанные данные
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Заказы не найдены'], 404);
        }

        return response()->json($orders);
    }


    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Показать заказ",
     *     description="Позволяет пользователю получить информацию о своем заказе.",
     *     operationId="getOrder",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные заказа",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизован"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     )
     * )
     */

    public function show($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        // Ищем заказ только для текущего пользователя
        $order = Order::where('user_id', $user->id)->with('items.product', 'items.size', 'address')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        return response()->json($order);
    }

    /**
     * @OA\Put(
     *     path="/orders/{id}/status",
     *     tags={"Orders"},
     *     summary="Обновить статус заказа",
     *     description="Позволяет авторизованному пользователю обновить статус своего заказа.",
     *     operationId="updateOrderStatus",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", description="Новый статус заказа", enum={"pending", "processed", "shipped", "delivered", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус заказа обновлен",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизован"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     )
     * )
     */

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403); // Возвращаем ошибку для пользователей без роли админа
        }

        // Ищем заказ по ID
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processed,shipped,delivered,cancelled',
        ]);

        // Обновляем статус заказа
        $order->update([
            'status' => $validated['status']
        ]);

        return response()->json($order);
    }

    /**
     * @OA\Get(
     *     path="/admin/orders",
     *     tags={"Orders"},
     *     summary="Получить все заказы",
     *     description="Позволяет администратору получить все заказы.",
     *     operationId="getAllOrders",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав"
     *     )
     * )
     */

    public function admStore(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403); // Возвращаем ошибку для пользователей без роли админа
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',  // Для админа мы указываем пользователя при создании заказа
            'address_id' => 'required|exists:addresses,id',
            'delivery' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Вычисляем общую цену
        $totalPrice = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $size = Size::find($item['size_id']);
            $price = $product->price; // Цена товара
            $totalPrice += $price * $item['quantity']; // Добавляем стоимость в общую сумму
        }

        // Создаем заказ
        $order = Order::create([
            'user_id' => $validated['user_id'],
            'address_id' => $validated['address_id'],
            'total_price' => $totalPrice,
            'status' => 'pending', // Начальный статус
            'delivery' => $validated['delivery'],
        ]);

        // Создаем позиции заказа
        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'size_id' => $item['size_id'],
                'quantity' => $item['quantity'],
                'price' => Product::find($item['product_id'])->price,
            ]);
        }

        return response()->json($order, 201);
    }

    /**
     * @OA\Get(
     *     path="/admin/orders/{id}",
     *     tags={"Orders"},
     *     summary="Показать заказ (для администратора)",
     *     description="Позволяет администратору получить информацию о заказе.",
     *     operationId="getOrderForAdmin",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные заказа",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     )
     * )
     */
    // Получить все заказы (для админа)
    public function admIndex()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403); // Проверка роли
        }

        $orders = Order::with('items.product', 'items.size', 'address')->get();
        return response()->json($orders);
    }

    /**
     * @OA\Post(
     *     path="/admin/orders",
     *     tags={"Orders"},
     *     summary="Создать заказ (для администратора)",
     *     description="Позволяет администратору создать заказ от имени пользователя.",
     *     operationId="createOrderForAdmin",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "address_id", "items"},
     *             @OA\Property(property="user_id", type="integer", description="ID пользователя, от имени которого создается заказ"),
     *             @OA\Property(property="address_id", type="integer", description="ID адреса доставки"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", description="ID продукта"),
     *                     @OA\Property(property="size_id", type="integer", description="ID размера продукта"),
     *                     @OA\Property(property="quantity", type="integer", description="Количество товара")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Создан заказ",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав"
     *     )
     * )
     */
    // Получить конкретный заказ (для админа)
    public function admShow($id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403); // Проверка роли
        }

        $order = Order::with('items.product', 'items.size', 'address')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        return response()->json($order);
    }

    /**
     * @OA\Put(
     *     path="/admin/orders/{id}",
     *     tags={"Orders"},
     *     summary="Обновить заказ (для администратора)",
     *     description="Позволяет администратору обновить заказ.",
     *     operationId="updateOrderForAdmin",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id", "items"},
     *             @OA\Property(property="address_id", type="integer", description="ID нового адреса доставки"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", description="ID продукта"),
     *                     @OA\Property(property="size_id", type="integer", description="ID размера продукта"),
     *                     @OA\Property(property="quantity", type="integer", description="Количество товара")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заказ обновлен",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     )
     * )
     */

    // Обновить заказ (для админа)
    public function admUpdate(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403); // Проверка роли
        }

        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        // Обновляем общую цену
        $totalPrice = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $size = Size::find($item['size_id']);
            $price = $product->price; // Цена товара
            $totalPrice += $price * $item['quantity']; // Добавляем стоимость в общую сумму
        }

        // Обновляем заказ
        $order->update([
            'address_id' => $validated['address_id'],
            'total_price' => $totalPrice,
        ]);

        // Обновляем позиции заказа
        foreach ($validated['items'] as $item) {
            $orderItem = OrderItem::where('order_id', $order->id)->where('product_id', $item['product_id'])->first();
            if ($orderItem) {
                $orderItem->update([
                    'quantity' => $item['quantity'],
                    'price' => Product::find($item['product_id'])->price,
                ]);
            } else {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'size_id' => $item['size_id'],
                    'quantity' => $item['quantity'],
                    'price' => Product::find($item['product_id'])->price,
                ]);
            }
        }

        return response()->json($order);
    }
    /**
     * @OA\Delete(
     *     path="/admin/orders/{id}",
     *     tags={"Orders"},
     *     summary="Удалить заказ (для администратора)",
     *     description="Позволяет администратору удалить заказ.",
     *     operationId="deleteOrderForAdmin",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заказ удален"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     )
     * )
     */

    // Удалить заказ (для админа)
    public function admDestroy($id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403); // Проверка роли
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        // Удаляем позиции заказа
        OrderItem::where('order_id', $id)->delete();

        // Удаляем заказ
        $order->delete();

        return response()->json(['message' => 'Заказ удален']);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{orderId}/confirm-payment",
     *     summary="Подтверждение оплаты заказа",
     *     description="Обновляет статус заказа на 'completed' после успешной оплаты.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID заказа, который необходимо подтвердить",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Оплата подтверждена, заказ завершен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Оплата прошла успешно, заказ завершен")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Заказ уже обработан",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Заказ уже обработан"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Заказ не найден"))
     *     )
     * )
     */

    public function confirmPayment(Request $request, $orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Заказ уже обработан'], 400);
        }

        $order->update(['status' => 'completed']);

        return response()->json(['message' => 'Оплата прошла успешно, заказ завершен']);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{orderId}/pay",
     *     summary="Создание платежа для заказа",
     *     description="Создает платеж через YooKassa и возвращает ссылку для подтверждения оплаты.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID заказа, который необходимо оплатить",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ссылка на подтверждение оплаты",
     *         @OA\JsonContent(
     *             @OA\Property(property="confirmation_url", type="string", example="https://yoomoney.ru/checkout/payments/v2/...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Оплата уже была произведена или заказ отменен",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Оплата уже была произведена или заказ отменен"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Не авторизован"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Заказ не найден"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка при создании платежа",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Ошибка при создании платежа"))
     *     )
     * )
     */

    public function payOrder(Request $request, $orderId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $order = Order::where('user_id', $user->id)->find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Оплата уже была произведена или заказ отменен'], 400);
        }

        $client = new Client();
        $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));

        try {
            $payment = $client->createPayment([
                'amount' => [
                    'value' => number_format($order->total_price, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'payment_method_data' => [
                    'type' => 'bank_card', // Можно заменить на sbp, yandex_split и т.д.
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => route('order.payment.success', ['orderId' => $order->id]),
                ],
                'capture' => true,
                'description' => "Оплата заказа #{$order->id}",
            ], uniqid('', true));

            return response()->json(['confirmation_url' => $payment->getConfirmation()->getConfirmationUrl()]);
        } catch (\Exception $e) {
            Log::error("Ошибка при создании платежа: " . $e->getMessage());
            return response()->json(['message' => 'Ошибка при создании платежа'], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/yookassa/webhook",
     *     summary="Webhook для обработки платежей ЮKassa",
     *     description="Обрабатывает уведомления от ЮKassa о статусе платежа и обновляет статус заказа.",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="object", type="object",
     *                 @OA\Property(property="id", type="string", example="2a3b5c7d-1234-5678-9abc-def012345678"),
     *                 @OA\Property(property="status", type="string", example="succeeded")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус платежа обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Статус платежа обновлен")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Некорректный запрос",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Некорректный запрос")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Заказ не найден")
     *         )
     *     )
     * )
     */

    public function yookassaWebhook(Request $request)
    {
        $data = $request->all();
        if (!isset($data['object']['id'])) {
            return response()->json(['message' => 'Некорректный запрос'], 400);
        }

        $paymentId = $data['object']['id'];
        $order = Order::where('payment_id', $paymentId)->first();

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        if ($data['object']['status'] === 'succeeded') {
            $order->update(['status' => 'completed']);
        } elseif ($data['object']['status'] === 'canceled') {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json(['message' => 'Статус платежа обновлен']);
    }

}
