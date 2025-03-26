<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SdekService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $account;

    public function __construct()
    {
        $this->baseUrl = config('services.sdek.base_url', 'https://api.cdek.ru/v2/');
        $this->apiKey  = config('services.sdek.api_key');
        $this->account = config('services.sdek.account');

        // В API v2 авторизация чаще всего осуществляется через Bearer-токен.
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$this->apiKey}",
            ],
            'timeout' => 10,
        ]);
    }

    /**
     * Расчет стоимости доставки через СДЭК (v2).
     *
     * @param array $params Массив параметров доставки, например:
     * [
     *   'senderCityId'   => 44,      // ID города отправителя
     *   'receiverCityId' => 137,     // ID города получателя
     *   'weight'         => 1.5,     // вес в кг
     *   'length'         => 30,      // длина в см
     *   'width'          => 20,      // ширина в см
     *   'height'         => 10,      // высота в см
     * ]
     *
     * @return array|null Возвращает данные расчёта тарифа или null при ошибке.
     */
    public function calculateDeliveryCost(array $params): ?array
{
    $endpoint = 'calculator/tariff';

    try {
        $response = $this->client->post($endpoint, [
            'json' => [
                'tariff_code' => 139, // Склад-дверь
                'from_location' => [
                    'code' => $params['senderCityId'],
                ],
                'to_location' => [
                    'code' => $params['receiverCityId'],
                ],
                'packages' => [
                    [
                        'weight' => (int)($params['weight'] * 1000), // Переводим кг в граммы
                        'length' => $params['length'],
                        'width'  => $params['width'],
                        'height' => $params['height'],
                    ]
                ]
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    } catch (GuzzleException $e) {
        Log::error('Ошибка расчёта доставки через СДЭК (v2)', ['error' => $e->getMessage()]);
        return null;
    }
}


    /**
     * Создание отправления (заказа) через СДЭК (v2).
     *
     * @param array $orderData Данные отправления согласно требованиям API СДЭК v2.
     *
     * Пример структуры $orderData:
     * [
     *   'number'      => 'ORDER-12345', // ваш идентификатор заказа
     *   'sender'      => [
     *         'name'     => 'ООО Ромашка',
     *         'address'  => 'г. Москва, ул. Ленина, д.1',
     *         'phone'    => '84991234567',
     *         // дополнительные поля отправителя...
     *   ],
     *   'receiver'    => [
     *         'name'     => 'Иван Иванов',
     *         'address'  => 'г. Санкт-Петербург, ул. Пушкина, д.10',
     *         'phone'    => '79991234567',
     *         // дополнительные поля получателя...
     *   ],
     *   'tariff_code' => 137, // код тарифа СДЭК
     *   'package'     => [
     *         'weight' => 1.5,
     *         'length' => 30,
     *         'width'  => 20,
     *         'height' => 10,
     *   ],
     *   // другие необходимые параметры...
     * ]
     *
     * @return array|null Возвращает данные созданного отправления или null при ошибке.
     */
    public function createShipment(array $orderData): ?array
    {
        // В API v2 для создания заказа используется эндпоинт orders
        $endpoint = 'orders';

        try {
            $response = $this->client->post($endpoint, [
                'json' => $orderData,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка создания отправления через СДЭК (v2)', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Отслеживание статуса отправления через СДЭК (v2).
     *
     * @param string $orderNumber Номер заказа, указанный в параметре number при создании отправления.
     * @return array|null Возвращает информацию о статусе отправления или null при ошибке.
     */
    public function trackShipment(string $orderNumber): ?array
    {
        // В API v2 для получения статуса заказа используется эндпоинт orders/status.
        // Например, можно передать параметр number через query.
        $endpoint = 'orders/status';

        try {
            $response = $this->client->get($endpoint, [
                'query' => ['number' => $orderNumber],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка отслеживания отправления через СДЭК (v2)', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
