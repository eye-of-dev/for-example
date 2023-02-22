<?php

namespace app\components\CdekComponent\resources;

class CalcAffordableRatesResource extends Base
{
    /**
     * Дата и время планируемой передачи заказа
     * По умолчанию - текущая
     * @var string
     */
    public string $date;

    /**
     * Тип заказа:
     * 1 - "интернет-магазин"
     * 2 - "доставка"
     * По умолчанию - 1
     * @var int
     */
    public int $type;

    /**
     * Валюта, в которой необходимо произвести расчет
     * По умолчанию - валюта договора
     * @var int
     */
    public int $currency;

    /**
     * Язык вывода информации о тарифах
     * Возможные значения: rus, eng, zho
     * По умолчанию - rus
     * @var string
     */
    public string $lang;

    /**
     * Адрес отправления
     * keys
     *  - code => integer (Код населенного пункта СДЭК (метод "Список населенных пунктов"))
     *  - postal_code => string (Почтовый индекс)
     *  - country_code => string (Код страны в формате ISO_3166-1_alpha-2)
     *  - city => string (Название города)
     *  - address => string (Полная строка адреса)
     * @var array
     */
    public array $from_location;

    /**
     * Адрес получения
     * keys
     *  - code => integer (Код населенного пункта СДЭК (метод "Список населенных пунктов"))
     *  - postal_code => string (Почтовый индекс)
     *  - country_code => string (Код страны в формате ISO_3166-1_alpha-2)
     *  - city => string (Название города)
     *  - address => string (Полная строка адреса)
     * @var array
     */
    public array $to_location;

    /**
     * Список информации по местам (упаковкам)
     * keys
     *  - weight => integer (Общий вес (в граммах))
     *  - length => integer (Габариты упаковки. Длина (в сантиметрах))
     *  - width => integer (Габариты упаковки. Ширина (в сантиметрах))
     *  - height => integer (Габариты упаковки. Высота (в сантиметрах))
     * @var array
     */
    public array $packages;

}