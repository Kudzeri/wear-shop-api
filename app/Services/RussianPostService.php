<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RussianPostService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $login;
    protected string $password;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl  = config('services.russian_post.base_url', 'https://otpravka-api.pochta.ru/');
        $this->apiKey   = config('services.russian_post.api_key');
        $this->login    = config('services.russian_post.login');
        $this->password = config('services.russian_post.password');
        $this->token    = config('services.russian_post.token');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'Content-Type'             => 'application/json',
                'Accept'                   => 'application/json',
                'Authorization'            => 'AccessToken ' . $this->apiKey,
                'X-User-Authorization'     => 'Basic ' . $this->token,
            ],
            'timeout' => 10,
        ]);
    }

    public function calculateDeliveryCost(array $params)
    {
        $endpoint = '1.0/tariff';

        try {
            $payload = [
               'index-from'    => $params['from_postcode'],
               'index-to'      => $params['to_postcode'],
               'mail-type'     => 'POSTAL_PARCEL',
               'mail-category' => 'ORDINARY',
               'mass'          => (int)($params['weight'] * 1000), // в граммах
               'dimension'     => [
                   'length' => (int)$params['length'],
                   'width'  => (int)$params['width'],
                   'height' => (int)$params['height'],
               ],
               'fragile' => true, // опционально
           ];

            $headers = [
                'Content-Type'           => 'application/json;charset=UTF-8',
                'Accept'                 => 'application/json;charset=UTF-8',
                'Authorization'          => 'AccessToken ' . $this->apiKey,
                'X-User-Authorization'   => 'Basic ' . base64_encode($this->login . ':' . $this->password),
            ];

            $response = $this->client->post($endpoint, [
                'headers' => $headers,
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
        $endpoint = '1.0/user/backlog';

        try {
            $payload = [[
                'order-num'        => $orderData['order_num'] ?? Str::uuid()->toString(),
                'index-to'         => $orderData['recipient']['postcode'],
                'place-to'         => $orderData['recipient']['city'],
                'region-to'        => $orderData['recipient']['region'],
                'street-to'        => $orderData['recipient']['street'],
                'house-to'         => $orderData['recipient']['house'],
                'room-to'          => $orderData['recipient']['room'] ?? null,
                'recipient-name'   => $orderData['recipient']['name'],
                'tel-address'      => $orderData['recipient']['phone'],
                'mail-type'        => 'POSTAL_PARCEL',
                'mail-category'    => 'ORDINARY',
                'mass'             => (int)($orderData['package']['weight'] * 1000),
                'dimension'        => [
                    'length' => (int) $orderData['package']['length'],
                    'width'  => (int) $orderData['package']['width'],
                    'height' => (int) $orderData['package']['height'],
                ],
                'declared-value'   => (int)($orderData['package']['value'] ?? 0),
                'fragile'          => $orderData['package']['fragile'] ?? false,
                'with-simple-notice' => true,
            ]];

            $headers = [
                'Content-Type'         => 'application/json;charset=UTF-8',
                'Accept'               => 'application/json',
                'Authorization'        => 'AccessToken ' . config('services.russian_post.access_token'),
                'X-User-Authorization' => 'Basic ' . base64_encode(config('services.russian_post.login') . ':' . config('services.russian_post.password')),
            ];

            $response = $this->client->put($endpoint, [
                'headers' => $headers,
                'json'    => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка создания отправления через Почту России (PUT /user/backlog)', [
                'error' => $e->getMessage()
            ]);
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

    public function findShipmentByOrderNumber(string $orderNumber): ?array
    {
        $endpoint = '1.0/backlog/search';

        try {
            $response = $this->client->get($endpoint, [
                'query' => ['query' => $orderNumber],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Ошибка поиска отправления через Почту России', ['error' => $e->getMessage()]);
            return null;
        }
    }

}
