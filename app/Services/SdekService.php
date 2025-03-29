<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SdekService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.sdek.base_url', 'https://api.cdek.ru/v2');
        $this->clientId = config('services.sdek.account');
        $this->clientSecret = config('services.sdek.api_key');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 10,
        ]);

        $this->accessToken = $this->getAccessToken();
    }

    protected function getAccessToken(): string
    {
        try {
            $response = $this->client->post('oauth/token', [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);


            return $data['access_token'] ?? '';
        } catch (GuzzleException $e) {
            Log::error('Ошибка получения access_token от СДЭК', ['error' => $e->getMessage()]);
            return '';
        }
    }

    protected function authorizedHeaders(): array
    {
        return [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$this->accessToken}",
        ];
    }

    public function calculateDeliveryCost(array $params): ?array
    {
        $endpoint = 'calculator/tarifflist';
        
        try {
            Log::info('Отдых не для разрабов: Время - '.now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z'));
            $response = $this->client->post($endpoint, [
                'headers' => $this->authorizedHeaders(),
                'json' => [
                    'type' => 1, // например, "дверь-дверь" = 1
                    'currency' => 1, // рубли
                    'lang' => 'rus',
                    'from_location' => [
                        'code' => $params['senderCityId'] ?? 0,
                        'postal_code' => $params['senderPostalCode'] ?? null,
                        'country_code' => $params['senderCountryCode'] ?? 'RU',
                        'city' => $params['senderCity'] ?? null,
                        'address' => $params['senderAddress'] ?? null,
                        'contragent_type' => $params['senderContragentType'] ?? 'sender',
                    ],
                    'to_location' => [
                        'code' => $params['receiverCityId'] ?? 0,
                        'postal_code' => $params['receiverPostalCode'] ?? null,
                        'country_code' => $params['receiverCountryCode'] ?? 'RU',
                        'city' => $params['receiverCity'] ?? null,
                        'address' => $params['receiverAddress'] ?? null,
                        'contragent_type' => $params['receiverContragentType'] ?? 'recipient',
                    ],
                    'packages' => [
                        [
                            'weight' => (int)($params['weight'] * 1000),
                            'length' => $params['length'],
                            'width'  => $params['width'],
                            'height' => $params['height'],
                        ]
                    ]
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка расчёта доставки через СДЭК (v2 - tarifflist)', ['error' => $e->getMessage()]);
            return null;
        }
    }


    public function createShipment(array $orderData): ?array
    {
        $endpoint = 'orders';

        try {
            $response = $this->client->post($endpoint, [
                'headers' => $this->authorizedHeaders(),
                'json' => $orderData,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка создания отправления через СДЭК (v2)', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function trackShipment(string $orderNumber): ?array
    {
        $endpoint = 'orders/status';

        try {
            $response = $this->client->get($endpoint, [
                'headers' => $this->authorizedHeaders(),
                'query'   => ['number' => $orderNumber],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка отслеживания отправления через СДЭК (v2)', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getShipmentByUuid(string $uuid): ?array
    {
        $endpoint = "orders/{$uuid}";

        try {
            $response = $this->client->get($endpoint, [
                'headers' => $this->authorizedHeaders(),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка получения информации о заказе по UUID через СДЭК', ['error' => $e->getMessage()]);
            return null;
        }
    }

}
