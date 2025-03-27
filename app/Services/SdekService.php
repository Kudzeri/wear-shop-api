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
        $this->baseUrl = config('services.sdek.base_url', 'https://api.cdek.ru/v2/');
        $this->clientId = config('services.sdek.client_id');
        $this->clientSecret = config('services.sdek.client_secret');

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
        $endpoint = 'calculator/tariff';

        try {
            $response = $this->client->post($endpoint, [
                'headers' => $this->authorizedHeaders(),
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
                            'weight' => (int)($params['weight'] * 1000), // кг → граммы
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
}
