<?php

namespace console\modules\update_cache\controllers;

use console\components\Controller;
use console\modules\update_cache\models\UpdateCache;


/**
 * Парсер обновления кеша на сайте.
 */
class ParserController extends Controller
{

    /**
     * Собираем страницы для обновления кеша
     * @return Null
     * @throws \Throwable
     */
    public function actionRun()
    {
        sleep(15);
        $model = new UpdateCache();
        $model->getLinks();
    }

}
