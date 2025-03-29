<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use YooKassa\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class YooKassaService
{
    protected Client $client;

    public function __construct()
    {
        $shopId = config('services.yookassa.shop_id');
        $secretKey = config('services.yookassa.secret_key');

        // Если параметры отсутствуют, не инициализируем клиент
        if ($shopId && $secretKey) {
            $this->client = new Client();
            $this->client->setAuth($shopId, $secretKey);
        }
    }

    /**
     * Создание платежа в YooKassa с fallback-логикой.
     *
     * @param float  $amount        Сумма платежа
     * @param string $description   Описание платежа
     * @param string $paymentMethod Метод оплаты (например, bank_card, sbp, installments)
     * @return mixed|null           Объект платежа или null при ошибке
     */
    public function createPayment(float $amount, string $description, string $paymentMethod, int $orderId)
    {
        // Если параметры подключения не заданы, возвращаем dummy-объект
        if (empty(config('services.yookassa.shop_id')) || empty(config('services.yookassa.secret_key')) || !isset($this->client)) {
            return new class {
                public function getId() {
                    return 'dummy-transaction-id';
                }
                public function getConfirmation() {
                    return new class {
                        public function getConfirmationUrl() {
                            return url('/dummy-payment');
                        }
                    };
                }
            };
        }

        try {
            $payment = $this->client->createPayment([
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'RUB'
                ],
                'payment_method_data' => ['type' => $paymentMethod],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => url('/successPayment?orderId=' . $orderId)
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
     * Отмена платежа по его идентификатору.
     *
     * @param string $paymentId Идентификатор платежа (transaction_id)
     * @return bool Успешно ли отменён платёж
     */
    public function cancelPayment(string $paymentId): bool
    {
        // Проверка наличия клиента
        if (!isset($this->client)) {
            Log::warning('YooKassa client не инициализирован для отмены платежа');
            return false;
        }

        try {
            $this->client->cancelPayment($paymentId);
            return true;
        } catch (Exception $e) {
            Log::error('Ошибка при отмене платежа YooKassa: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Инициализация платежа для заказа.
     *
     * Этот метод является обёрткой над методом createPayment и
     * формирует описание платежа на основе данных заказа.
     *
     * @param mixed $order  Объект заказа, используется для формирования описания (например, order->id)
     * @param float $amount Сумма платежа
     * @return mixed|null   Объект платежа или null при ошибке
     */
    public function initiatePayment(Order $order, float $amount)
    {
        $description = "Оплата заказа #{$order->id}";
        return $this->createPayment($amount, $description, 'bank_card');
    }

    /**
     * Получение статуса платежа по transaction_id.
     *
     * @param string $paymentId Идентификатор платежа (transaction_id)
     * @return mixed|null       Информация о платеже или null при ошибке
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
