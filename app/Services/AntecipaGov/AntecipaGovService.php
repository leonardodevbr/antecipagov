<?php

namespace App\Services\AntecipaGov;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class AntecipaGovService
{
    /**
     * @return string|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken()
    {
        if (env('API_ANTECIPAGOV_IS_TRIAL')) {
            return '06aef429-a981-3ec5-a1f8-71d38d86481e';
        }

        try {
            $client = new Client();

            $options = [
                'verify' => env('APP_ENV') != 'local',
                'headers' => [
                    'Authorization' => "Basic " . base64_encode(env("API_ANTECIPAGOV_KEY") . ':' . env("API_ANTECIPAGOV_SECRET"))
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ];

            $response = $client->post('https://gateway.apiserpro.serpro.gov.br/token', $options);
            $responseBody = json_decode($response->getBody()->getContents());

            if (isset($responseBody->access_token) && !empty($responseBody->access_token) && $responseBody->token_type === 'Bearer') {
                return $responseBody->access_token;
            }

        } catch (ClientException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    /**
     * @param $uri
     * @param array $body
     * @param string $method
     */
    public function makeRequestPilar($uri, $body = [], $method = 'POST')
    {
        $url = env('API_ANTECIPAGOV_BASE_URL_PILAR') . $uri;
        return $this->doRequest($url, $body, $method);
    }

    /**
     * @param $uri
     * @param array $body
     * @param string $method
     * @return array
     */
    public function makeRequestPortal($uri, $body = [], $method = 'POST')
    {
        $url = env('API_ANTECIPAGOV_BASE_URL_PORTAL') . $uri;
        return $this->doRequest($url, $body, $method);
    }

    public function doRequest($url, $body = [], $method = 'POST')
    {
        try {
            $client = new Client();

            $options = [
                'verify' => env('APP_ENV') != 'local',
                'headers' => [
                    'Authorization' => "Bearer " . $this->getToken(),
                    'Content-Type' => 'application/json',
                    'accept' => '*/*'
                ]
            ];

            if (!empty($body)) {
                if ($method === 'POST') {
                    $options['body'] = json_encode($body);
                }

                if ($method === 'GET') {
                    $options['query'] = $body;
                }
            }

            $response = $client->request($method, $url, $options);

            return [
                'success' => true,
                'data' => json_decode($response->getBody()->getContents())
            ];

        } catch (ClientException | ServerException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => $e->getResponse()->getBody()->getContents()
            ];
        }
    }

    /**
     * @param $message
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function reponseSuccess($message, $data = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * @param $message
     * @param array $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function reponseError($message, $data = [], $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
