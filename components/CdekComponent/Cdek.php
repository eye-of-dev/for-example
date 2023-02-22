<?php

namespace app\components\CdekComponent;

use app\components\CdekComponent\request\RequestComponent;
use app\components\CdekComponent\resources\CalcAffordableRatesResource;
use app\components\CdekComponent\resources\ListSettlementsResource;
use app\components\CdekComponent\resources\OrderInfoResource;
use app\components\CdekComponent\resources\OrderRegistrationResource;
use app\components\CdekComponent\services\Auth;
use app\components\CdekComponent\services\CalcAffordableRates;
use app\components\CdekComponent\services\ListSettlements;
use app\components\CdekComponent\services\OrderInfo;
use app\components\CdekComponent\services\OrderRegistration;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\Component;

class Cdek extends Component
{
    /**
     * Account/Идентификатор
     * @var string
     */
    public string $client_id = '';

    /**
     * Secure password/Пароль
     * @var string
     */
    public string $client_secret = '';

    /**
     * Режим работы
     * 0 - тестовый режим
     * 1 - боевой режим
     * @var integer
     */
    public int $mode = 0;

    /**
     * Адрес и код города отправления
     * @var array
     */
    public array $from_location = [];

    /**
     * Адрес и код города отправления
     * @var array
     */
    public array $sender = [];

    /**
     * @var string
     */
    private string $test_ulr = 'https://api.edu.cdek.ru/v2';

    /**
     * @var string
     */
    private string $test_client_id = 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI';

    /**
     * @var string
     */
    private string $test_client_secret = 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG';

    /**
     * @var string
     */
    private string $ulr = 'https://api.cdek.ru/v2';

    /**
     * @var string|null
     */
    private ?string $auth_token;

    /**
     * @var RequestComponent
     */
    private RequestComponent $request;

    /**
     * @throws GuzzleException
     */
    public function __construct($config = [])
    {
        $this->request = new RequestComponent;

        $this->auth_token = (new Auth(
            ($config['mode'] == 1) ? $this->ulr : $this->test_ulr,
            $this->request
        ))->sendRequest(
            ($config['mode'] == 1) ? $config['client_id'] : $this->test_client_id,
            ($config['mode'] == 1) ? $config['client_secret'] : $this->test_client_secret);

        parent::__construct($config);
    }

    /**
     * @param CalcAffordableRatesResource $income_params
     * @return mixed|null
     * @throws GuzzleException
     */
    public function getCalcAffordableRates(CalcAffordableRatesResource $income_params)
    {
        return (new CalcAffordableRates(
            (!$this->mode) ? $this->test_ulr : $this->ulr,
            $this->request,
            $this->auth_token
        ))->sendRequest($income_params);
    }

    /**
     * @param OrderRegistrationResource $income_params
     * @return null[]
     * @throws GuzzleException
     */
    public function createOrder(OrderRegistrationResource $income_params)
    {
        return (new OrderRegistration(
            (!$this->mode) ? $this->test_ulr : $this->ulr,
            $this->request,
            $this->auth_token
        ))->sendRequest($income_params);
    }

    /**
     * @param OrderInfoResource $income_params
     * @return null[]
     * @throws GuzzleException
     */
    public function orderInfo(OrderInfoResource $income_params)
    {
        return (new OrderInfo(
            (!$this->mode) ? $this->test_ulr : $this->ulr,
            $this->request,
            $this->auth_token
        ))->sendRequest($income_params);
    }

    /**
     * @param ListSettlementsResource $income_params
     * @return array
     * @throws GuzzleException
     */
    public function getSettlements(ListSettlementsResource $income_params): array
    {
        return (new ListSettlements(
            (!$this->mode) ? $this->test_ulr : $this->ulr,
            $this->request,
            $this->auth_token
        ))->sendRequest($income_params);
    }
}