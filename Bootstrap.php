<?php

namespace kmergen\eshop;

use Yii;
use yii\authclient\Collection;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

/**
 * Bootstrap class registers module and log component
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{

    /** @inheritdoc */
    public function bootstrap($app)
    {
        /** @var Module $module */
        /** @var \yii\db\ActiveRecord $modelName */
        if ($app->hasModule('eshop') && ($module = $app->getModule('eshop')) instanceof Module) {

            // Set the log targets
//            $app->getLog()->targets[] = [
//                'class' => 'yii\log\FileTarget',
//                'categories' => ['paypal'],
//                'logFile' => '@frontend/runtime/logs/eshop.log',
//            ];

        }
    }

}
