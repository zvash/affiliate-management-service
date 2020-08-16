<?php

namespace App\Services;

use App\Click;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;

class DCMService
{
    /**
     * AuthService constructor.
     */
    public function __construct()
    {
        $baseUrl = rtrim(env('DCM_REGISTER_CLICK_URL', ''), '/');
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

    /**
     * @param Click $click
     * @return array
     */
    public function registerClick(Click $click)
    {
        $affiliateConfig = config('affiliate');
        $params = [
            'offer_id' => $click->offer_id,
            'aff_id' => $affiliateConfig['affiliate_id'],
            'aff_click_id' => $click->token
        ];
        $url = rtrim(env('DCM_REGISTER_CLICK_URL', ''), '/');
        foreach ($params as $key => $value) {
            $url = add_query_param_to_url($url, "$key=$value");
        }
        try {
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' => $this->headers
                ]
            );
            if ($response->getStatusCode() == 200) {
                $contents = json_decode($response->getBody()->getContents(), 1);
                return ['data' => $contents['data'], 'status' => 200];
            }
            return ['data' => ['json' => $response->getBody()->getContents()], 'status' => $response->getStatusCode()];
        } catch (GuzzleException $exception) {
            return ['data' => $exception->getResponse()->getBody()->getContents(), 'status' => $exception->getCode()];
        }
    }


}