<?php

namespace app\components;

use common\models\PartnerLink;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\ServerException;
use Throwable;
use ValueError;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

class AnyProxyComponent extends Component
{
    /**
     * Запрос через прокси
     */
    const PROXY_CF = 'cf';

    /**
     * Запрос через curl
     */
    const PROXY_VPN = 'vpn';

    /**
     * @var string
     */
    public string $partner_link;

    /**
     * @var string
     */
    public string $proxy_url;

    /**
     * @var integer
     */
    public int $timeout = 5;

    /**
     * @var bool|string
     */
    public bool|string $interface = false;

    /**
     *
     * @var string|null
     */
    private ?string $base_url = null;

    /**
     * @var array
     */
    private array $agents = [
        'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X; en-US) AppleWebKit/535.14.3 (KHTML, like Gecko) Version/3.0.5 Mobile/8B113 Safari/6535.14.3',
        'Mozilla/5.0 (X11; Linux i686) AppleWebKit/5310 (KHTML, like Gecko) Chrome/38.0.804.0 Mobile Safari/5310',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0 rv:2.0; sl-SI) AppleWebKit/535.40.4 (KHTML, like Gecko) Version/4.1 Safari/535.40.4',
        'Opera/8.62 (Windows CE; en-US) Presto/2.12.321 Version/10.00',
        'Opera/9.31 (Windows NT 6.1; en-US) Presto/2.9.318 Version/11.00',
        'Mozilla/5.0 (Linux; Android 7.0; NS-P10A8100 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
        'Mozilla/5.0 (Linux; Android 4.4.2; Lenovo B8000-F Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.158 Safari/537.36',
        'Mozilla/5.0 (X11; Linux i686) AppleWebKit/5310 (KHTML, like Gecko) Chrome/39.0.826.0 Mobile Safari/5310',
        'Opera/8.68 (X11; Linux x86_64; sl-SI) Presto/2.11.339 Version/11.00',
        'Mozilla/5.0 (Linux; Android 8.0.0; SM-G960F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.84 Mobile Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/69.0.3497.105 Mobile/15E148 Safari/605.1',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/13.2b11866 Mobile/16A366 Safari/605.1.15',
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1',
        'Mozilla/5.0 (Windows Phone 10.0; Android 4.2.1; Xbox; Xbox One) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Mobile Safari/537.36 Edge/13.10586'
    ];

    public function init()
    {
        $this->base_url = $this->getPartnerLink();

        parent::init();
    }

    /**
     * @param string $type
     * @param array $params
     * @return array|null[]
     * @throws GuzzleException
     */
    public function Get1x2_VZip(string $type, array $params = []): array
    {
        $url = sprintf('%s/service-api/LiveFeed/Get1x2_VZip', $this->base_url);
        $this->parseParams($url, $params);

        switch ($type) {
            case static::PROXY_CF:
                $url = str_replace('https://', '', $url);
                return $this->sendRequestWithProxy($url);
            case static::PROXY_VPN:
                $referer = sprintf('%s/ru/live/ice-hockey', $this->base_url);
                return $this->sendRequestWithVpn($url, $referer);
        }

        return [];
    }

    /**
     * @param string $type
     * @param array $params
     * @return array|null[]
     * @throws GuzzleException
     */
    public function GetSportsShortZip(string $type, array $params = []): array
    {
        $url = sprintf('%s/service-api/LiveFeed/GetSportsShortZip', $this->base_url);
        $this->parseParams($url, $params);

        switch ($type) {
            case static::PROXY_CF:
                $url = str_replace('https://', '', $url);
                return $this->sendRequestWithProxy($url);
            case static::PROXY_VPN:
                $referer = sprintf('%s/ru/resultsframe/?cdnOff=1', $this->base_url);
                return $this->sendRequestWithVpn($url, $referer);
        }

        return [];
    }


    /**
     * @param $type
     * @param array $params
     * @return mixed|null
     * @throws GuzzleException
     */
    public function getResults($type, array $params = []): mixed
    {
        $url = sprintf('%s/web-api/results/getmain', $this->base_url);
        $this->parseParams($url, $params);

        switch ($type) {
            case static::PROXY_CF:
                $url = str_replace('https://', '', $url);
                return $this->sendRequestWithProxy($url);
            case static::PROXY_VPN:
                $referer = sprintf('%s/ru/resultsframe/?cdnOff=1', $this->base_url);
                return $this->sendRequestWithVpn($url, $referer);
        }

        return [];
    }

    /**
     * @param $url
     * @return mixed|null
     */
    public function getLocRedir($url): mixed
    {
        try {
            usleep(10);

            echo $url . PHP_EOL;

            $agents = $this->agents;
            shuffle($agents);

            $interface = '';
            if($this->interface) {
                $interface = sprintf('--interface %s', $this->interface);
            }

            $curl_string = sprintf('sudo curl "%s" -Ls -o /dev/null %s -w %s --connect-timeout %s', $url, $interface, '%{url_effective}', $this->timeout);

            exec($curl_string, $content, $code);

            if ($code == 0 && !empty($content)) {
                return $content[0];
            }
        } catch (Throwable $e) {
            echo 'Exception ' . $e->getMessage() . PHP_EOL;
            Yii::$app->errorHandler->logException($e);
        }

        return null;
    }

    /**
     * @param string $uri
     * @return array
     * @throws GuzzleException
     */
    private function sendRequestWithProxy(string $uri): array
    {
        $retry = 1;
        oxb_req:
        try {

            $base_uri = $this->proxy_url;
            echo sprintf('%s/%s', $base_uri, $uri) . PHP_EOL;

            $client = new Client(['base_uri' => $base_uri, 'timeout' => 15.0]);
            $response = $client->request('GET', $uri, ['headers' => ['Accept-Encoding' => 'gzip']]);

            $code = $response->getStatusCode();

            if ($code == 200) {
                $body = $response->getBody();

                $headers = $response->getHeaders();

                $content = $body->getContents();
                if (str_starts_with($content, '{')) {
                    $content = Json::decode($content);

                    return ['headers' => $headers, 'content' => $content];
                }
            }
        } catch (InvalidArgumentException $e) {
            echo 'InvalidArgumentException ' . PHP_EOL;
            if ($retry <= 3) {
                $retry += 1;
                usleep(1000);
                goto oxb_req;
            }
            Yii::$app->errorHandler->logException($e);
        } catch (ClientException|ServerException $e) {
            if ($e->getResponse()->getStatusCode() === 504) {
                exit;
            }
            throw $e;
        } catch (ConnectException $e) {
            echo 'ConnectException ' . PHP_EOL;
            if ($retry <= 3) {
                $retry += 1;
                usleep(1000);
                goto oxb_req;
            }

            $cacheKey = [__METHOD__, 'connect_exception'];
            $connect_exception = Yii::$app->cache->get($cacheKey);
            if (!$connect_exception) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->cache->set($cacheKey, true, 10 * TIME_MINUTE);
            }
            throw $e;
        } catch (GuzzleException $e) {
            echo 'GuzzleException ' . PHP_EOL;
            throw $e;
        }

        return ['headers' => null, 'content' => null];
    }

    /**
     * @param string $url
     * @param string $referer
     * @return null[]
     */
    private function sendRequestWithVpn(string $url, string $referer): array
    {
        try {
            usleep(10);
            echo $url . PHP_EOL;

            $agents = $this->agents;
            shuffle($agents);

            $interface = '';
            if ($this->interface) {
                $interface = sprintf('--interface %s', $this->interface);
            }

            $curl_string = sprintf('sudo curl "%s" \
                          -H "authority: %s" \
                          -H "accept: application/json, text/plain, */*" \
                          -H "accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7" \
                          -H "cache-control: no-cache" \
                          -H "pragma: no-cache" \
                          -H "referer: %s" \
                          -H "user-agent: %s" \
                          -H "x-requested-with: XMLHttpRequest" \
                          %s \
                          --compressed --insecure --connect-timeout %s', $url, parse_url($this->base_url, PHP_URL_HOST), $referer, $agents[array_rand($agents)], $interface, $this->timeout);

            exec($curl_string, $content, $code);

            if ($code == 0 && $content) {
                return ['headers' => null, 'content' => Json::decode($content['0'])];
            }

        } catch (ValueError $e) {
            echo 'ValueError ' . PHP_EOL;
            Yii::$app->errorHandler->logException($e);
        }

        return ['headers' => null, 'content' => null];
    }

    /**
     * @param string $url
     * @param array $params
     * @return void
     */
    private function parseParams(string &$url, array $params): void
    {
        if (!empty($params)) {
            $url .= (!str_contains($url, '?')) ? '?' : '&';
            $url .= http_build_query($params);
        }
    }

    /**
     * @return mixed
     */
    private function getPartnerLink(): mixed
    {
        if (!filter_var($this->partner_link, FILTER_VALIDATE_URL)) {
            return PartnerLink::getCurrentLink($this->partner_link);
        } else {
            return $this->partner_link;
        }
    }
}
