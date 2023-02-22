<?php

namespace app\components\CdekComponent\services;

use app\components\CdekComponent\request\RequestComponent;
use GuzzleHttp\Exception\GuzzleException;
use Yii;

/**
 * Авторизация клиентов
 * https://confluence.cdek.ru/pages/viewpage.action?pageId=29923918
 */
class Auth
{
    /**
     * @var array
     */
    private array $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    /**
     * @var string
     */
    private string $method = 'POST';

    /**
     * @var string
     */
    private string $base_url = '';

    /**
     * @var string
     */
    private string $part_url = 'oauth/token?parameters';

    /**
     * @var RequestComponent
     */
    private RequestComponent $request;

    public function __construct(string $url, RequestComponent $request)
    {
        $this->base_url = sprintf('%s/%s', $url, $this->part_url);
        $this->request = $request;
    }

    /**
     * @param string $client_id
     * @param string $client_secret
     * @return string
     * @throws GuzzleException
     */
    public function sendRequest(string $client_id, string $client_secret): ?string
    {
        $response = $this->request->sendRequest(
            $this->base_url,
            $this->method,
            [
                'grant_type' => 'client_credentials',
                'client_id' => $client_id,
                'client_secret' => $client_secret
            ],
            $this->headers
        );
        return $response['content']['access_token'] ?? null;
    }

}