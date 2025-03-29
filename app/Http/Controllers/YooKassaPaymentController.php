<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\YooKassaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class YooKassaPaymentController extends Controller
{
    protected YooKassaService $yooKassaService;

    public function __construct(YooKassaService $yooKassaService)
    {
        $this->yooKassaService = $yooKassaService;
    }

    // Инициирует платёж для заказа, который ожидает оплаты
    public function initiate(Request $request, $orderId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }
        $order = Order::where('user_id', $user->id)
                      ->where('status', 'awaiting_payment')
                      ->find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Заказ не найден или не ожидает оплаты'], 404);
        }
        $payment = $this->yooKassaService->initiatePayment($order, $order->total_price);
        if (!$payment || !isset($payment->id)) {
            return response()->json(['message' => 'Ошибка при создании платежа'], 500);
        }
        // Опционально обновляем заказ с идентификатором платежа
        $order->update(['payment_id' => $payment->id]);

        return response()->json([
            'message' => 'Платеж создан',
            'payment_url' => $payment->confirmation->confirmation_url,
        ]);
    }
}
