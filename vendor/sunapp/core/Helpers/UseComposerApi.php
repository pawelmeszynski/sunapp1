<?php

namespace SunAppModules\Core\Helpers;

class UseComposerApi
{
    public static function getApi($url)
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get($url);
        $response = $request->getBody();
        return $response;
    }


    public static function postApi($url, $body)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request("POST", $url, ['form_params' => $body]);
        $response = $client->send($response);
        return $response;
    }
}
