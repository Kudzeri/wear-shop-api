<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class RussianPostService
{
    protected Client $client;
    protected string $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // Эти параметры рекомендуется вынести в конфигурацию (.env)
        $this->baseUrl  = config('services.russian_post.base_url', 'https://otpravka-api.pochta.ru/');
        $this->apiKey   = env('RUSSIAN_POST_API_KEY');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'timeout' => 10,
        ]);
    }

    /**
     * Расчет стоимости доставки через Почту России.
     *
     * @param array $params Массив с параметрами доставки, например:
     * [
     *    'from_postcode' => '101000', // почтовый индекс отправителя
     *    'to_postcode'   => '190000', // почтовый индекс получателя
     *    'weight'        => 1.5,       // вес в кг
     *    'length'        => 30,        // длина в см
     *    'width'         => 20,        // ширина в см
     *    'height'        => 10,        // высота в см
     * ]
     *
     * @return array|null Возвращает данные расчета или null при ошибке.
     */
    public function calculateDeliveryCost(array $params): ?array
    {
        // Пример эндпоинта – уточните его в документации API Почты России
        $endpoint = '1.0/calculate';

        try {
            $response = $this->client->post($endpoint, [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка расчёта стоимости доставки через Почту России', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Создание заказа (отправления) через Почту России.
     *
     * @param array $orderData Данные заказа согласно требованиям API.
     *
     * Пример структуры $orderData:
     * [
     *    'recipient' => [
     *         'name'       => 'Иван Иванов',
     *         'postcode'   => '190000',
     *         'address'    => 'г. Санкт-Петербург, ул. Ленина, д.1',
     *         'phone'      => '79991234567',
     *         // другие поля...
     *    ],
     *    'sender' => [
     *         'name'       => 'ООО Ромашка',
     *         'postcode'   => '101000',
     *         'address'    => 'г. Москва, ул. Пушкина, д.10',
     *         'phone'      => '84951234567',
     *         // другие поля...
     *    ],
     *    'package' => [
     *         'weight' => 1.5,
     *         'length' => 30,
     *         'width'  => 20,
     *         'height' => 10,
     *         // дополнительные параметры упаковки...
     *    ],
     *    // другие параметры заказа...
     * ]
     *
     * @return array|null Возвращает данные созданного заказа или null при ошибке.
     */
    public function createShipment(array $orderData): ?array
    {
        // Эндпоинт для создания отправления – уточните его в документации
        $endpoint = '1.0/otpravka';

        try {
            $response = $this->client->post($endpoint, [
                'json' => $orderData,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка создания отправления через Почту России', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Отслеживание статуса отправления по штрих-коду (tracking number).
     *
     * @param string $trackingNumber Штрих-код отправления.
     * @return array|null Возвращает информацию о статусе отправления или null при ошибке.
     */
    public function trackShipment(string $trackingNumber): ?array
    {
        // Эндпоинт для отслеживания – уточните его в документации
        $endpoint = '1.0/track';

        try {
            $response = $this->client->get($endpoint, [
                'query' => ['barcode' => $trackingNumber],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка отслеживания отправления через Почту России', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // Новый метод для обращения к API Почты России
    public function getData()
    {
        // Замените 'some-endpoint' на нужный endpoint API
        $response = $this->client->get('some-endpoint');
        return json_decode($response->getBody(), true);
    }
}
