<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class RussianPostService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $login;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl  = config('services.russian_post.base_url', 'https://otpravka-api.pochta.ru/');
        $this->apiKey   = config('services.russian_post.api_key');
        $this->login    = config('services.russian_post.login');
        $this->password = config('services.russian_post.password');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'Content-Type'             => 'application/json',
                'Accept'                   => 'application/json',
                'Authorization'            => 'AccessToken ' . $this->apiKey,
                'X-User-Authorization'     => 'Basic ' . base64_encode("{$this->login}:{$this->password}"),
            ],
            'timeout' => 10,
        ]);
    }

    public function calculateDeliveryCost(array $params): ?array
    {
        $endpoint = '1.0/tariff';

        try {
            $payload = [
                'index-from'    => $params['from_postcode'],
                'index-to'      => $params['to_postcode'],
                'weight'        => (int)($params['weight'] * 1000),
                'mail-type'     => 2, // посылка
                'mail-category' => 0, // обычная
            ];

            $response = $this->client->post($endpoint, [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка расчёта доставки через Почту России', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function createShipment(array $orderData): ?array
    {
        $endpoint = '1.0/backlog';

        try {
            $payload = [[
                'address-type-to' => 'DEFAULT',
                'index-to'        => $orderData['recipient']['postcode'],
                'recipient-name'  => $orderData['recipient']['name'],
                'recipient-phone' => $orderData['recipient']['phone'],
                'mail-type'       => 2,
                'mail-category'   => 0,
                'weight'          => (int)($orderData['package']['weight'] * 1000),
                'dimension'       => [
                    'length' => $orderData['package']['length'],
                    'width'  => $orderData['package']['width'],
                    'height' => $orderData['package']['height'],
                ],
                'declared-value' => $orderData['package']['value'] ?? 0,
            ]];

            $response = $this->client->post($endpoint, [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка создания отправления через Почту России', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function trackShipment(string $trackingNumber): ?array
    {
        $client = new Client([
            'base_uri' => 'https://tracking.pochta.ru/',
        ]);

        try {
            $response = $client->get('tracking-web/rest/api/v1/track', [
                'query' => [
                    'rt'      => 'JSON',
                    'barcode' => $trackingNumber,
                ],
                'headers' => [
                    'Authorization' => 'AccessToken ' . $this->apiKey,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка отслеживания отправления через Почту России', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
