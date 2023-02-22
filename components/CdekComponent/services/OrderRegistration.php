<?php

namespace app\components\CdekComponent\services;

use app\components\CdekComponent\request\RequestComponent;
use app\components\CdekComponent\resources\CalcAffordableRatesResource;
use app\components\CdekComponent\resources\OrderRegistrationResource;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Регистрация заказа
 * https://confluence.cdek.ru/pages/viewpage.action?pageId=29923926
 */
class OrderRegistration
{
    /**
     * @var array
     */
    private array $headers = [
        'Authorization' => '',
        'Content-Type' => 'application/json'
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
    private string $part_url = 'orders';

    /**
     * @var RequestComponent
     */
    private RequestComponent $request;

    public function __construct(string $url, RequestComponent $request, string $auth_token)
    {
        $this->headers['Authorization'] = sprintf('Bearer %s', $auth_token);
        $this->base_url = sprintf('%s/%s', $url, $this->part_url);
        $this->request = $request;
    }

    /**
     * Запрос к провайдеру для создания заказа
     * @param OrderRegistrationResource $resource
     * @return array|null[]
     * @throws GuzzleException
     */
    public function sendRequest(OrderRegistrationResource $resource): array
    {
        if ($resource->validate()) {
            return $this->request->sendRequest(
                $this->base_url,
                $this->method,
                get_object_vars($resource),
                $this->headers
            )['content'];
        }

        return [];
    }
}