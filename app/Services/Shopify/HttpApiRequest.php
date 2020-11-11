<?php

namespace App\Services\Shopify;

use Illuminate\Support\Facades\Log;


class HttpApiRequest
{

    public static function getContificoApi($type) {

        #$uri = "https://amigui.contifico.com/sistema/api/v1"."/".$type;
        $uri = "https://api.contifico.com/sistema/api/v1"."/".$type;

        try {

            $client = new \GuzzleHttp\Client();
            $response =  $client->request('GET',
                $uri, [
                    'headers'        => ['Authorization' => '0sfwtkHrPOOzPMCe0lS7JiMMXSz3prcfZjzinWgUyYo']
                    ,'verify'        => false,
                ]);

            return  json_decode($response->getBody()->getContents(), TRUE);

        } catch (\Exception $ex) {
            Log::error('getContificoApi API '. $type. $ex->getMessage() . $ex->getLine() . $ex->getFile());
            return null;
        }
    }



    public static function ShopifyApiHandler($method, $url, $data = []) {

        $apiUrl = "https://api.sello.io/v5".$url;
        try {
            $client = new \GuzzleHttp\Client();
            # Send an asynchronous request.
            $body = json_encode($data);
            $request = new \GuzzleHttp\Psr7\Request($method, $apiUrl,
                ['Authorization' => ''],
                $body
            );

            $promise = $client->sendAsync($request)->then(function ($response) {
                $data =  json_decode($response->getBody()->getContents(), TRUE);
                if(isset($data['errorType']) && $data['errorType'] == 'TypeError') return false;
                else if (empty($data) && $response->getStatusCode() === 200) return true;
                return $data;
            });
            return $promise->wait();
        } catch (\Exception $ex) {
            \Log::error('Sello  API '.$url .' '. $method . ' Method Request Failed '.'' . $ex->getMessage());
            return false;
        }
    }
}
