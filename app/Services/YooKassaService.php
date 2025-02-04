<?php

namespace App\Services;

use YooKassa\Client;

class YooKassaService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));
    }

    public function createPayment($amount, $description, $paymentMethod)
    {
        $payment = $this->client->createPayment([
            'amount' => ['value' => $amount, 'currency' => 'RUB'],
            'payment_method_data' => ['type' => $paymentMethod], // "bank_card", "sbp", "installments"
            'confirmation' => ['type' => 'redirect', 'return_url' => 'https://your-site.ru/success'],
            'capture' => true,
            'description' => $description,
        ], uniqid('', true));

        return $payment;
    }
}
