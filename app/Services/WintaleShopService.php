<?php

namespace App\Services;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WintaleShopService
{
    public function __construct()
    {
        $baseUrl = rtrim(env('WINTALE_SHOP_BASE_URL', ''), '/');
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $this->client = new Client(
            [
                'base_uri' => $baseUrl,
                'timeout' => config('timeout', 5)
            ]
        );
    }

    public function getOrders(int $offerId, string $orderDate,?string $phone, ?string $email)
    {
        if ($phone) {
            $phone = substr($phone, -10);
        }
        $payload = [
            'product_id' => $offerId,
            'order_date' => $orderDate,
            'phone' => $phone,
            'email' => $email
        ];

        try {
            $response = $this->client->request(
                'POST',
                $this->getClaimUrl(),
                [
                    'json' => $payload,
                    'headers' => $this->headers
                ]
            );
            if ($response->getStatusCode() == 200) {
                $contents = json_decode($response->getBody()->getContents(), 1);
                dd($contents);
                return ['data' => $contents['data'], 'status' => 200];
            }
        } catch (GuzzleException $exception) {
            return ['data' => $exception->getResponse()->getBody()->getContents(), 'status' => $exception->getCode()];
        }
        return [];
    }

    private function getClaimUrl()
    {
        return 'wp-json/api/v1/claim';
    }
    
}