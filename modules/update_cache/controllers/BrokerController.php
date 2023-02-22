<?php

namespace console\modules\update_cache\controllers;

use Yii;
use console\components\Controller;
use console\modules\update_cache\models\UpdateCache;

/**
 * Парсер обновления кеша страниц
 */
class BrokerController extends Controller
{

    public function actionTest()
    {
        $module = $this->module;

        $model = new UpdateCache();
        $callback = function ($msg) use (&$model, $module) {
            $model->update($msg->body, $module);
            Yii::$app->rabbit_component->set_delivered($msg);
        };

        Yii::$app->rabbit_component->get_message($model->rabbit_queue, $callback);
    }

    /**
     * Парсинг матчей в DB в кол-во потоков активных парсеров
     * @return Null
     */
    public function actionRun()
    {
        \parallel\bootstrap(Yii::getAlias('@yii_for_thread'));

        $model = new UpdateCache();
        $module = $this->module;
        while (true) {
            list($queue_name, $message_count, $consumer_count) = Yii::$app->rabbit_component->get_status_queue($model->rabbit_queue);
            if ($message_count > 0) {
                for ($i = 0; $i < $module->max_threads - $consumer_count; $i++) {
                    \parallel\run($this->multiParseGames(), [$queue_name]);
                }
            }

            sleep(20);
        }

    }

    /**
     * @return callback
     */
    private function multiParseGames()
    {
        return function ($queue_name) {
            $model = new UpdateCache();
            $module = Yii::$app->getModule('update-cache');
            $callback = function ($msg) use (&$model, &$module) {
                $model->update($msg->body, $module);
                Yii::$app->rabbit_component->set_delivered($msg);
            };
            Yii::$app->rabbit_component->get_message($queue_name, $callback);
        };
    }

}
