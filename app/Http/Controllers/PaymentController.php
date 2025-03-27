<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\YooKassaService;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class PaymentController extends Controller
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
     *     path="/api/payments",
     *     summary="Создание платежа",
     *     description="Создает новый платеж через YooKassa с учетом скидок и баллов лояльности.",
     *     tags={"Payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "payment_method"},
     *             @OA\Property(property="amount", type="number", example=1500.00, description="Сумма платежа"),
     *             @OA\Property(property="order_id", type="integer", example=123, description="ID заказа"),
     *             @OA\Property(property="payment_method", type="string", enum={"bank_card", "sbp", "installments"}, description="Способ оплаты"),
     *             @OA\Property(property="use_loyalty_points", type="boolean", example=true, description="Использовать баллы лояльности")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Платеж успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Платеж создан"),
     *             @OA\Property(property="payment_url", type="string", example="https://yoomoney.ru/checkout/payments/v2/..."),
     *             @OA\Property(property="discount_applied", type="object", description="Информация о примененной скидке")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Ошибка валидации"),
     *     @OA\Response(response=401, description="Неавторизованный доступ"),
     *     @OA\Response(response=500, description="Ошибка при создании платежа")
     * )
     */

    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:bank_card,sbp,installments',
            'order_id' => 'required|integer|exists:orders,id',
            'use_loyalty_points' => 'boolean'
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Неавторизованный доступ'], 401);
        }

        $totalAmount = $request->amount;

        $discountData = $request->use_loyalty_points
            ? $this->loyaltyService->applyDiscount($user, $totalAmount)
            : ['final_amount' => $totalAmount];

        if ($discountData['final_amount'] <= 0) {
            return response()->json(['message' => 'Сумма после скидки не может быть 0'], 400);
        }

        $payment = $this->yooKassaService->createPayment(
            $discountData['final_amount'],
            'Оплата заказа пользователем ' . $user->id,
            $request->payment_method
        );

        if (!$payment) {
            return response()->json(['message' => 'Ошибка при создании платежа'], 500);
        }

        $paymentRecord = Payment::create([
            'user_id' => $user->id,
            'order_id' => $request->order_id,
            'amount' => $discountData['final_amount'],
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'transaction_id' => $payment->getId(),
        ]);

        return response()->json([
            'message' => 'Платеж создан',
            'payment_url' => $payment->getConfirmation()->getConfirmationUrl(),
            'discount_applied' => $discountData
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/webhook",
     *     summary="Webhook для обработки платежей",
     *     description="Обрабатывает уведомления от YooKassa и обновляет статус платежа.",
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
     *     @OA\Response(response=200, description="Webhook обработан"),
     *     @OA\Response(response=400, description="Неверные данные"),
     *     @OA\Response(response=404, description="Платеж не найден")
     * )
     */

    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();

        if (!isset($data['object']['id'])) {
            return response()->json(['message' => 'Неверные данные'], 400);
        }

        $payment = Payment::where('transaction_id', $data['object']['id'])->first();

        if ($payment) {
            $payment->status = $data['object']['status'];
            $payment->save();

            if ($payment->status === 'succeeded') {
                $this->loyaltyService->addPoints($payment->user, floor($payment->amount * 0.05)); // 5% от суммы
            } elseif ($payment->status === 'refunded') {
                $this->loyaltyService->redeemPoints($payment->user, floor($payment->amount * 0.05)); // Вернуть баллы за отмененный заказ
            }
        }

        return response()->json(['message' => 'Webhook обработан']);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/status",
     *     summary="Проверка статуса платежа",
     *     description="Позволяет проверить статус платежа по `transaction_id`.",
     *     tags={"Payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="transaction_id",
     *         in="query",
     *         required=true,
     *         description="ID транзакции платежа",
     *         @OA\Schema(type="string", example="2a3b5c7d-1234-5678-9abc-def012345678")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус платежа",
     *         @OA\JsonContent(
     *             @OA\Property(property="transaction_id", type="string", example="2a3b5c7d-1234-5678-9abc-def012345678"),
     *             @OA\Property(property="status", type="string", example="succeeded")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Платеж не найден"),
     *     @OA\Response(response=500, description="Ошибка при получении статуса платежа")
     * )
     */

    public function checkPaymentStatus(Request $request): JsonResponse
    {
        $request->validate(['transaction_id' => 'required|string']);

        $payment = Payment::where('transaction_id', $request->transaction_id)->first();

        if (!$payment) {
            return response()->json(['message' => 'Платеж не найден'], 404);
        }

        $paymentStatus = $this->yooKassaService->getPaymentStatus($request->transaction_id);

        if (!$paymentStatus) {
            return response()->json(['message' => 'Ошибка при получении статуса платежа'], 500);
        }

        return response()->json([
            'transaction_id' => $request->transaction_id,
            'status' => $paymentStatus->getStatus()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/cancel",
     *     summary="Отмена платежа",
     *     description="Позволяет отменить платеж, если он еще не завершен.",
     *     tags={"Payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id"},
     *             @OA\Property(property="transaction_id", type="string", example="2a3b5c7d-1234-5678-9abc-def012345678", description="ID транзакции платежа")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Платеж успешно отменен",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Платеж успешно отменен"))
     *     ),
     *     @OA\Response(response=400, description="Платеж нельзя отменить"),
     *     @OA\Response(response=404, description="Платеж не найден"),
     *     @OA\Response(response=500, description="Ошибка при отмене платежа")
     * )
     */

    public function cancelPayment(Request $request): JsonResponse
    {
        $request->validate(['transaction_id' => 'required|string']);

        $payment = Payment::where('transaction_id', $request->transaction_id)->first();

        if (!$payment) {
            return response()->json(['message' => 'Платеж не найден'], 404);
        }

        if ($payment->status !== 'pending') {
            return response()->json(['message' => 'Платеж нельзя отменить'], 400);
        }

        $cancelResponse = $this->yooKassaService->cancelPayment($request->transaction_id);

        if (!$cancelResponse) {
            return response()->json(['message' => 'Ошибка при отмене платежа'], 500);
        }

        $payment->status = 'canceled';
        $payment->save();

        return response()->json(['message' => 'Платеж успешно отменен']);
    }
}
