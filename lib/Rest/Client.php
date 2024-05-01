<?php

namespace Custom\ZKTConnect\Rest;

use Bitrix\Main\Web\HttpClient;

class Client
{
    protected string $host = '';
    protected string $endpoint = '';
    protected string $username = '';
    protected string $password = '';
    protected HttpClient $httpClient;

    public function __construct(string $host, string $username, string $password)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $this->host . '/';

        $this->httpClient = new HttpClient([
            'socketTimeout' => 10,
            'streamTimeout' => 30,
            'waitarray' => true,
            'version' => HttpClient::HTTP_1_1,
            'headers' => []
        ]);

        $headers = $this->prepareHeaders();

        $this->httpClient->setHeaders($headers);
    }

    private function prepareHeaders(): array
    {
        $headers = [];

        $token = $this->getToken($this->username, $this->password);

        if (!empty($token)) {
            $headers['Authorization'] = 'Token ' . $token;
        }

        $headers['Content-Type'] = 'application/json';

        return $headers;
    }

    public function getToken(string $username, string $password): ?string
    {
        $response = $this->post('api-token-auth', [
            'username' => $username,
            'password' => $password
        ]);

        return $response['token'] ?? null;
    }

    public function getTransactionList(?string $dateStart = null, ?string $dateEnd = null): array
    {
        $params = [
            'page_size' => 10000000000
        ];

        if (!empty($dateStart)) {
            $params['start_time'] = $dateStart;
        }

        if (!empty($dateEnd)) {
            $params['end_time'] = $dateEnd;
        }

        $response =  $this->get('iclock.api.transactions', $params);

        return $response['data'] ?? [];
    }

    private function post(string $action = '', array $postData = [], bool $multipart = false): array
    {
        $actionUrl = str_replace('.', '/', $action);

        $methodUrl = $this->endpoint . $actionUrl . '/';

        $jsonResponse = $this->httpClient->post($methodUrl, $postData, $multipart);

        $response = \json_decode($jsonResponse, true);

        if (!$response) {
            $response = [];
        }

        return $response;
    }

    private function get(string $action = '', array $params = []): array
    {
        $actionUrl = str_replace('.', '/', $action);

        $methodUrl = $this->endpoint . $actionUrl . '/';

        $params = array_filter($params);

        $separator = '?';

        foreach ($params as $param => $value) {
            $value = str_replace(' ', '+', $value);

            $methodUrl .= $separator . $param . '=' . $value;

            if ($separator != '&') {
                $separator = '&';
            }
        }

        $jsonResponse = $this->httpClient->get($methodUrl);
        
        $response = \json_decode($jsonResponse, true);
        
        if (!$response) {
            $response = [];
        }

        return $response;
    }
}
