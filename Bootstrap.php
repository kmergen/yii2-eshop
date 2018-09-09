<?php
/*
 * This file is part of the yii2-media project.
 *
 * (c) Yii2-media project <http://github.com/kmergen/yii2-media/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace kmergen\eshop;

use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

/**
 * Bootstrap for eshop module
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{

    /** @inheritdoc */
    public function bootstrap($app)
    {
        if ($app->hasModule('eshop') && ($module = $app->getModule('eshop')) instanceof Module) {

            $app->get('i18n')->translations['eshop*'] = [
                'class' => PhpMessageSource::class,
                'basePath' => __DIR__ . '/messages',
            ];
        }

        $app->getFormatter()->numberFormatterSymbols = [\NumberFormatter::CURRENCY_SYMBOL => '€'];
    }

}
