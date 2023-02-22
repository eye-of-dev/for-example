<?php

namespace app\components\CdekComponent\request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\InvalidArgumentException;
use yii\base\Component;
use yii\helpers\Json;

class RequestComponent extends Component
{
    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return array
     * @throws GuzzleException
     */
    public function sendRequest(string $url, string $method, array $data, array $headers): array
    {
        try {
            $client = new Client();
            $response = $client->request($method, $url, $this->parseData($data, $headers));

            $body = $response->getBody();
            $content = $body->getContents();
            return ['headers' => $response->getHeaders(), 'content' => Json::decode($content)];

        } catch (InvalidArgumentException $e) {
            echo 'InvalidArgumentException ' . PHP_EOL;
        } catch (ConnectException $e) {
            echo 'ConnectException ' . PHP_EOL;
        } catch (GuzzleException $e) {
            echo 'GuzzleException ' . PHP_EOL;
            throw $e;
        }

        return ['headers' => [], 'content' => []];
    }

    /**
     * @param array $data
     * @param array $headers
     * @return array|array[]
     */
    private function parseData(array $data, array $headers): array
    {
        switch ($headers['Content-Type']) {
            case 'application/json':
                return ['body' => Json::encode($data), 'headers' => $headers];
            case 'application/x-www-form-urlencoded':
                return ['form_params' => $data, 'headers' => $headers];
        }

        return ['headers' => $headers];
    }
}
