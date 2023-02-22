<?php

namespace app\components\CdekComponent\resources;

class OrderInfoResource extends Base
{
    /**
     * Идентификатор заказа в ИС СДЭК, по которому необходима информация
     * /v2/orders/{uuid}
     * @var string
     */
    public string $uuid;

    /**
     * Номер заказа СДЭК, по которому необходима информация
     * /v2/orders?cdek_number={cdek_number}
     * @var string
     */
    public string $cdek_number;

    /**
     * Номер заказа в ИС Клиента, по которому необходима информация
     * /v2/order?_im_number={im_number}
     * @var string
     */
    public string $number;

    /**
     * Тип идентификатора
     * @var int
     */
    public int $type = 1;

}