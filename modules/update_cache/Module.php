<?php

namespace console\modules\update_cache;

/**
 * update_cache module definition class
 */
class Module extends \yii\base\Module
{

    /**
     * @var int
     */
    public $sleep;

    /**
     * @var int
     */
    public $max_threads;

    /**
     * @var int
     */
    public $max_bad;

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'console\modules\update_cache\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
