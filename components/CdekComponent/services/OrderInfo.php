<?php

namespace app\components\CdekComponent\services;

use app\components\CdekComponent\request\RequestComponent;
use app\components\CdekComponent\resources\OrderInfoResource;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Информация о заказе
 * https://confluence.cdek.ru/pages/viewpage.action?pageId=29923975
 */
class OrderInfo
{
    /**
     * @var array
     */
    private array $headers = [
        'Authorization' => '',
        'Content-Type' => ''
    ];

    /**
     * @var string
     */
    private string $base_url = '';

    /**
     * @var string
     */
    private string $method = 'GET';

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
     * @param OrderInfoResource $resource
     * @return array|null[]
     * @throws GuzzleException
     */
    public function sendRequest(OrderInfoResource $resource): array
    {
        if ($resource->validate()) {
            return $this->request->sendRequest(
                $this->prepareUrl($resource),
                $this->method,
                [],
                $this->headers
            );
        }

        return [];
    }

    /**
     * @param OrderInfoResource $resource
     * @return string
     */
    private function prepareUrl(OrderInfoResource $resource): string
    {
        switch ($resource->type) {
            case 1 :
                return sprintf('%s/%s', $this->base_url, $resource->uuid);
            case 3:
            case 2 :
                return sprintf('%s?%s', $this->base_url, http_build_query(get_object_vars($resource)));
        }

        return sprintf('%s/%s', $this->base_url, $resource->uuid);
    }
}