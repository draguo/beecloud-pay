<?php

namespace Draguo\Pay\Supports;

use GuzzleHttp\Client;

trait HttpRequest
{
    protected function post($uri, $params = [])
    {
        $client = new Client();
        return $client->post($uri, [
            'form_params' => $params,
        ])->getBody()->getContents();
    }
}