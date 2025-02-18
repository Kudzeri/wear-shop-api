<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\RussianPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class OrderController extends Controller
{
    protected OrderService $orderService;

    /**
     * OrderController constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Создание заказа",
     *     description="Создает заказ и соответствующий платеж для авторизованного пользователя. Платеж осуществляется через YooKassa (yookassa). При выборе YooKassa инициируется запрос к API для получения transaction_id.",
     *     operationId="storeOrder",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для создания заказа",
     *         @OA\JsonContent(
     *             required={"address_id", "items", "delivery", "payment_method"},
     *             @OA\Property(
     *                 property="address_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID адреса доставки"
     *             ),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="Список товаров заказа",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id", "size_id", "quantity"},
     *                     @OA\Property(
     *                         property="product_id",
     *                         type="integer",
     *                         example=10,
     *                         description="ID продукта"
     *                     ),
     *                     @OA\Property(
     *                         property="size_id",
     *                         type="integer",
     *                         example=2,
     *                         description="ID размера"
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="integer",
     *                         example=3,
     *                         description="Количество товара"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="delivery",
     *                 type="string",
     *                 example="express",
     *                 description="Тип доставки"
     *             ),
     *             @OA\Property(
     *                 property="use_loyalty_points",
     *                 type="boolean",
     *                 example=false,
     *                 description="Флаг использования бонусных баллов"
     *             ),
     *             @OA\Property(
     *                 property="payment_method",
     *                 type="string",
     *                 enum={"yookassa"},
     *                 example="yookassa",
     *                 description="Метод оплаты: 'yookassa' - через YooKassa"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заказ успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="order",
     *                 type="object",
     *                 description="Объект созданного заказа"
     *             ),
     *             @OA\Property(
     *                 property="payment",
     *                 type="object",
     *                 description="Объект созданного платежа, содержащий transaction_id, полученный от API YooKassa",
     *                 @OA\Property(property="id", type="integer", example=100),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="order_id", type="integer", example=50),
     *                 @OA\Property(property="amount", type="number", format="float", example=1500.75),
     *                 @OA\Property(property="currency", type="string", example="RUB"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="payment_method", type="string", example="yookassa"),
     *                 @OA\Property(property="transaction_id", type="string", example="yookassa_5f8c9e7a6b8f7")
     *             ),
     *             @OA\Property(
     *                 property="discount_applied",
     *                 type="object",
     *                 description="Информация о примененной скидке",
     *                 @OA\Property(property="final_amount", type="number", format="float", example=1500.75)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Некорректные данные запроса или логическая ошибка (например, сумма заказа после скидки равна 0)",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Список товаров пуст"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Не авторизован"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации входящих данных",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера или ошибка при инициализации платежа через YooKassa",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Ошибка сервера"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Подробное описание ошибки"
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $validated = $request->validate([
            'address_id'          => 'required|exists:addresses,id',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.size_id'     => 'required|exists:sizes,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'delivery'            => 'required|string',
            'use_loyalty_points'  => 'boolean',
            'payment_method'      => 'required|string|in:yookassa',
        ]);

        try {
            $result = $this->orderService->createOrder($validated, $user);
            return response()->json($result, 201);
        } catch (\Exception $e) {
            $status = in_array($e->getMessage(), [
                'Список товаров пуст',
                'Сумма заказа после скидки не может быть 0'
            ]) ? 400 : 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
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

        $orders = Order::where('user_id', $user->id)
            ->with('items.product', 'items.size', 'address')
            ->get();

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

        $order = Order::where('user_id', $user->id)
            ->with('items.product', 'items.size', 'address')
            ->find($id);

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
     *             @OA\Property(property="payment_url", type="string", example="https://yoomoney.ru/checkout/payments/v2/...")
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

        $order = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден или уже оплачен'], 400);
        }

        // Предполагается, что для создания платежа используется метод createPayment в сервисе YooKassa.
        $payment = $this->orderService->yooKassaService->createPayment(
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
            // Добавляем бонусные баллы (например, 5% от суммы заказа)
            $this->orderService->loyaltyService->addPoints($order->user, floor($order->total_price * 0.05));
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

        $order = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден или уже обработан'], 400);
        }

        $order->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Заказ отменен']);
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
    public function confirmPayment(Request $request, $orderId): JsonResponse
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
     * @OA\Get(
     *     path="/admin/orders",
     *     tags={"Orders"},
     *     summary="Получить все заказы (для администратора)",
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
    public function admIndex(): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $orders = Order::with('items.product', 'items.size', 'address')->get();
        return response()->json($orders);
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
    public function admShow($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
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
    public function admUpdate(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'items'      => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id'    => 'required|exists:sizes,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        try {
            $updatedOrder = $this->orderService->updateOrder($order, $validated);
            return response()->json($updatedOrder);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
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
    public function admDestroy($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        // Удаляем позиции заказа (если связь настроена, можно использовать метод items())
        $order->items()->delete();
        $order->delete();

        return response()->json(['message' => 'Заказ удален']);
    }

    public function calculateDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_postcode' => 'required|string',
            'to_postcode'   => 'required|string',
            'weight'        => 'required|numeric',
            'length'        => 'required|numeric',
            'width'         => 'required|numeric',
            'height'        => 'required|numeric',
        ]);

        $russianPostService = app(RussianPostService::class);
        $result = $russianPostService->calculateDeliveryCost($validated);

        if (!$result) {
            return response()->json(['message' => 'Ошибка расчёта доставки'], 500);
        }

        return response()->json($result);
    }

}
