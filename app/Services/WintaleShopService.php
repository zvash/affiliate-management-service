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
                'timeout' => config('timeout', 30)
            ]
        );
    }

    public function getOrders(int $offerId, string $orderDate,?string $phone, ?string $email = null)
    {
        if ($phone) {
            $phone = substr($phone, -9);
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
                return [
                    'data' => is_numeric($contents) ? $contents * 1 : $contents,
                    'status' => 200
                ];
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