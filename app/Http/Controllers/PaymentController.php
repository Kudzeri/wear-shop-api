<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\YooKassaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected YooKassaService $yooKassaService;

    public function __construct(YooKassaService $yooKassaService)
    {
        $this->yooKassaService = $yooKassaService;
    }

    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:bank_card,sbp,installments',
        ]);

        $payment = $this->yooKassaService->createPayment(
            $request->amount,
            'Оплата заказа пользователем ' . Auth::id(),
            $request->payment_method
        );

        // Сохраняем платеж в БД
        $paymentRecord = Payment::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'transaction_id' => $payment->getId(),
        ]);

        return response()->json([
            'message' => 'Платеж создан',
            'payment_url' => $payment->getConfirmation()->getConfirmationUrl(),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();
        $payment = Payment::where('transaction_id', $data['object']['id'])->first();

        if ($payment) {
            $payment->status = $data['object']['status'];
            $payment->save();
        }

        return response()->json(['message' => 'Webhook обработан']);
    }
}
