<?php

namespace App\Services;

use Exception;
use YooKassa\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class YooKassaService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));
    }

    /**
     * Создание платежа в YooKassa
     *
     * @param float $amount Сумма платежа
     * @param string $description Описание платежа
     * @param string $paymentMethod Метод оплаты (bank_card, sbp, installments)
     * @return mixed
     */
    public function createPayment(float $amount, string $description, string $paymentMethod)
    {
        try {
            $payment = $this->client->createPayment([
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'RUB'
                ],
                'payment_method_data' => ['type' => $paymentMethod],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => config('services.yookassa.return_url')
                ],
                'capture' => true,
                'description' => $description,
            ], Str::uuid()->toString());

            return $payment;
        } catch (Exception $e) {
            Log::error('Ошибка создания платежа YooKassa: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение статуса платежа по transaction_id
     *
     * @param string $paymentId
     * @return mixed|null
     */
    public function getPaymentStatus(string $paymentId)
    {
        try {
            return $this->client->getPaymentInfo($paymentId);
        } catch (Exception $e) {
            Log::error('Ошибка получения статуса платежа YooKassa: ' . $e->getMessage());
            return null;
        }
    }
}
