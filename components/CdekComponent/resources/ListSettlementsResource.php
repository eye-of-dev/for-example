<?php

namespace app\components\CdekComponent\resources;

class ListSettlementsResource extends Base
{
    /**
     * Массив кодов стран в формате  ISO_3166-1_alpha-2
     * @var string
     */
    public string $country_codes;

    /**
     * Код региона СДЭК
     * @var int
     */
    public int $region_code;

    /**
     * Уникальный идентификатор ФИАС населенного пункта
     * @var int
     */
    public int $fias_guid;

    /**
     * Почтовый индекс
     * @var string
     */
    public string $postal_code;

    /**
     * Код населенного пункта СДЭК
     * @var int
     */
    public int $code;

    /**
     * Название населенного пункта. Должно соответствовать полностью
     * @var string
     */
    public string $city;

    /**
     * Ограничение выборки результата. По умолчанию 1000
     * @var int
     */
    public int $size;

    /**
     * Локализация. По умолчанию "rus"
     * @var string
     */
    public string $lang;

}