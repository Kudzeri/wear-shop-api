<?php

namespace App\Http\Controllers;

    use App\Models\Order;
    use App\Models\OrderItem;
    use App\Models\Payment;
    use App\Models\Product;
    use App\Models\Size;
    use App\Services\YooKassaService;
    use App\Services\LoyaltyService;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected YooKassaService $yooKassaService;
    protected LoyaltyService $loyaltyService;

    public function __construct(YooKassaService $yooKassaService, LoyaltyService $loyaltyService)
    {
        $this->yooKassaService = $yooKassaService;
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Создание заказа",
     *     description="Создает новый заказ с возможностью использования баллов лояльности.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id", "items", "delivery"},
     *             @OA\Property(property="address_id", type="integer", example=1, description="ID адреса доставки"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer", example=5, description="ID товара"),
     *                 @OA\Property(property="size_id", type="integer", example=2, description="ID размера"),
     *                 @OA\Property(property="quantity", type="integer", example=3, description="Количество товара")
     *             )),
     *             @OA\Property(property="delivery", type="string", example="Стандартная доставка", description="Тип доставки"),
     *             @OA\Property(property="use_loyalty_points", type="boolean", example=true, description="Использовать баллы лояльности")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заказ успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="order", type="object", description="Детали заказа"),
     *             @OA\Property(property="payment", type="object", description="Детали платежа"),
     *             @OA\Property(property="discount_applied", type="object", description="Примененная скидка")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Ошибка валидации"),
     *     @OA\Response(response=401, description="Не авторизован")
     * )
     */

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery' => 'required|string',
            'use_loyalty_points' => 'boolean'
        ]);

        // Рассчитываем стоимость заказа
        $totalPrice = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $totalPrice += $product->price * $item['quantity'];
        }

        // Применяем скидки и баллы
        $discountData = $validated['use_loyalty_points']
            ? $this->loyaltyService->applyDiscount($user, $totalPrice)
            : ['final_amount' => $totalPrice];

        if ($discountData['final_amount'] <= 0) {
            return response()->json(['message' => 'Сумма заказа после скидки не может быть 0'], 400);
        }

        // Создаем заказ
        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $validated['address_id'],
            'total_price' => $discountData['final_amount'],
            'status' => 'pending',
            'delivery' => $validated['delivery'],
        ]);

        // Создаем товары в заказе
        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'size_id' => $item['size_id'],
                'quantity' => $item['quantity'],
                'price' => Product::find($item['product_id'])->price,
            ]);
        }

        // Создаем запись о платеже
        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => $discountData['final_amount'],
            'status' => 'pending',
            'payment_method' => null,
            'transaction_id' => null
        ]);

        return response()->json([
            'order' => $order,
            'payment' => $payment,
            'discount_applied' => $discountData
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Получение всех заказов пользователя",
     *     description="Возвращает список всех заказов текущего пользователя.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(response=401, description="Не авторизован")
     * )
     */

    public function index(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $orders = Order::where('user_id', $user->id)->with('items.product', 'items.size', 'address')->get();
        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Получение конкретного заказа",
     *     description="Возвращает информацию о заказе пользователя.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Информация о заказе", @OA\JsonContent(ref="#/components/schemas/Order")),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=404, description="Заказ не найден")
     * )
     */

    public function show($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $order = Order::where('user_id', $user->id)->with('items.product', 'items.size', 'address')->find($id);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        return response()->json($order);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{orderId}/pay",
     *     summary="Оплата заказа",
     *     description="Создает платеж через YooKassa и возвращает ссылку для подтверждения оплаты.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ссылка на подтверждение оплаты",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Платеж создан"),
     *             @OA\Property(property="payment_url", type="string", example="https://yoomoney.ru/checkout/payments/v2/..."),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Заказ уже оплачен или отменен"),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=404, description="Заказ не найден"),
     *     @OA\Response(response=500, description="Ошибка при создании платежа")
     * )
     */

    public function payOrder($orderId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $order = Order::where('user_id', $user->id)->where('status', 'pending')->find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден или уже оплачен'], 400);
        }

        $payment = $this->yooKassaService->createPayment(
            $order->total_price,
            "Оплата заказа #{$order->id}",
            'bank_card'
        );

        if (!$payment) {
            return response()->json(['message' => 'Ошибка при создании платежа'], 500);
        }

        $order->update(['payment_id' => $payment->getId()]);

        return response()->json([
            'message' => 'Платеж создан',
            'payment_url' => $payment->getConfirmation()->getConfirmationUrl()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/webhook",
     *     summary="Webhook для обработки платежей",
     *     description="Обрабатывает уведомления от YooKassa и обновляет статус заказа.",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="object", type="object",
     *                 @OA\Property(property="id", type="string", example="2a3b5c7d-1234-5678-9abc-def012345678"),
     *                 @OA\Property(property="status", type="string", example="succeeded")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Статус платежа обновлен"),
     *     @OA\Response(response=400, description="Некорректный запрос"),
     *     @OA\Response(response=404, description="Заказ не найден")
     * )
     */

    public function webhook(Request $request): JsonResponse
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
            $this->loyaltyService->addPoints($order->user, floor($order->total_price * 0.05));
        } elseif ($data['object']['status'] === 'canceled') {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json(['message' => 'Статус платежа обновлен']);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{orderId}/cancel",
     *     summary="Отмена заказа",
     *     description="Позволяет пользователю отменить заказ, если он еще не обработан.",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Заказ отменен", @OA\JsonContent(@OA\Property(property="message", type="string", example="Заказ отменен"))),
     *     @OA\Response(response=400, description="Заказ уже обработан"),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=404, description="Заказ не найден")
     * )
     */

    public function cancelOrder($orderId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $order = Order::where('user_id', $user->id)->where('status', 'pending')->find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден или уже обработан'], 400);
        }

        $order->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Заказ отменен']);
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

}
