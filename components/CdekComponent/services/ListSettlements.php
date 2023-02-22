<?php

namespace app\components\CdekComponent\services;

use app\components\CdekComponent\request\RequestComponent;
use app\components\CdekComponent\resources\ListSettlementsResource;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Список населенных пунктов
 * https://confluence.cdek.ru/pages/viewpage.action?pageId=33829437
 */
class ListSettlements
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
    private string $part_url = 'location/cities/';

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
     * Запрос к провайдеру для получения данных
     * @param ListSettlementsResource $resource
     * @return array
     * @throws GuzzleException
     */
    public function sendRequest(ListSettlementsResource $resource): array
    {
        if ($resource->validate()) {
            return $this->request->sendRequest(
                sprintf('%s?%s', $this->base_url, http_build_query(get_object_vars($resource))),
                $this->method,
                [],
                $this->headers
            );
        }

        return [];
    }
}