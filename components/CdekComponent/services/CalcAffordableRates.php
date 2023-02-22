<?php

namespace app\components\CdekComponent\services;

use app\components\CdekComponent\request\RequestComponent;
use app\components\CdekComponent\resources\CalcAffordableRatesResource;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Калькулятор. Расчет по доступным тарифам
 * https://confluence.cdek.ru/pages/viewpage.action?pageId=63345519
 */
class CalcAffordableRates
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
    private string $part_url = 'calculator/tarifflist';

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
     * @param CalcAffordableRatesResource $resource
     * @return mixed|null
     * @throws GuzzleException
     */
    public function sendRequest(CalcAffordableRatesResource $resource)
    {
        if ($resource->validate()) {
            $response = $this->request->sendRequest(
                $this->base_url,
                $this->method,
                get_object_vars($resource),
                $this->headers
            );

            //if sdek returns error
            if(isset($response['content']['errors'])){
                return $response['content']['errors'][0];
            }

            $tariff_code = $this->getTariff($response['content']['tariff_codes']);

            return $tariff_code ?? null;
        }

        return null;
    }

    /**
     * @param array $tariff_codes
     * @param int $tariff_code
     * @param int $delivery_mode
     * @return mixed
     */
    private function getTariff(array $tariff_codes, int $tariff_code = 480, int $delivery_mode = 1)
    {
        usort($tariff_codes, function ($item1, $item2) {
            return $item1['delivery_sum'] <=> $item2['delivery_sum'];
        });

        foreach ($tariff_codes as $tariff) {
            if ($tariff['tariff_code'] == $tariff_code) {
                return $tariff;
            }
        }

        foreach ($tariff_codes as $tariff) {
            if ($tariff['delivery_mode'] == $delivery_mode) {
                return $tariff;
            }
        }

        return $tariff_codes[0];
    }

}