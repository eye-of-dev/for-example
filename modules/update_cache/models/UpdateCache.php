<?php

namespace console\modules\update_cache\models;

use common\modules\sitemap\models\SitemapResult;
use Throwable;
use Yii;
use yii\base\Module;
use yii\db\ActiveRecord;
use yii\db\Expression;

class UpdateCache
{
    /**
     *
     * @var string
     */
    public string $rabbit_queue = 'update-cache';

    /**
     *
     * @var string
     */
    private string $rabbit_router = 'update-cache-router';

    /**
     *
     * @var Object
     */
    private $rabbit_client = null;

    public function __construct($config = [])
    {
        $this->rabbit_client = Yii::$app->rabbit_component;
        $this->rabbit_client->unique_channel($this->rabbit_queue, $this->rabbit_router);
    }

    /**
     * @throws Throwable
     */
    public function getLinks()
    {
        try {
            $pages = $this->get_all_pages_ids();
            foreach ($pages as &$page) {
                echo sprintf('Страница %s добавлена в очередь', $page['id']) . PHP_EOL;

                $this->rabbit_client->set_unique_message(serialize($page), $this->rabbit_router, $page['id']);
            }
        } catch (Throwable $ex) {
            if (YII_ENV === 'dev') {
                throw $ex;
            }
            Yii::error($ex, 'update-cache');
        }
    }

    public function update(string $data, Module $module)
    {
        try {
            $domains = Yii::$app->params['domains'];
            $page = unserialize($data);

            $url = sprintf('%s%s', $domains[$page['domain']], $page['url']);

            usleep($module->sleep * 1000);
            $http_code = $this->get_with_curl($url);
            if (in_array($http_code, [200, 301, 302, 403, 404])) {
                echo sprintf('Кеш для страницы %s обновлен', $page['id']) . PHP_EOL;
                SitemapResult::deleteAll(['id' => $page['id']]);
            } else {
                echo sprintf('Кеш для страницы %s не обновлен', $page['id']) . PHP_EOL;
                SitemapResult::updateAll([
                    'cnt_bad' => $page['cnt_bad'] + 1,
                    'http_code' => $http_code,
                    'is_bad' => $page['cnt_bad'] + 1 >= $module->max_bad,
                ], [
                    'id' => $page['id'],
                ]);
            }
        } catch (Throwable $ex) {
            if (YII_ENV === 'dev') {
                throw $ex;
            }
            Yii::error($ex, 'update-cache');
        }
    }

    /**
     * @return array|SitemapResult[]|ActiveRecord[]
     */
    private function get_all_pages_ids(): array
    {
        return SitemapResult::find()
            ->select(['id', 'domain', 'url', 'cnt_bad'])
            ->andFilterWhere([
                'is_bad' => ((date('H') % 2 === 0) && (date('i') === 0))? null : 0,
            ])
            ->orderBy(['priority' => SORT_DESC, 'date' => SORT_DESC])
            ->asArray()
            ->all();
    }

    /**
     * @todo Передалать на GuzzleHttp\Client. Вывести отсюда
     */
    private function get_with_curl(string &$url)
    {
        $headers = [
            'accept' => '*/*',
            'accept-encoding' => 'gzip,deflate',
            'cache-control' => 'no-cache',
            'pragma' => 'no-cache',
            'Z-Update' => 1
        ];
        $agent = 'Mozilla/5.0 (compatible; OverbettingBot)';

        echo 'Curl ' . $url . PHP_EOL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo 'Curl ' . $url . ': ' . $http_code . PHP_EOL;
        return $http_code;
    }
}