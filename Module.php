<?php

namespace kmergen\eshop;

use Yii;
use yii\i18n\PhpMessageSource;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class Module extends \yii\base\Module
{
    public $defaultRoute = 'dashboard';

    public $paymentMethods = [];

    public $currencySign = '€';

    public function init()
    {
        parent::init();
        $this->registerTranslations();


        $paymentMethodsDefault = [
            'paypal_rest' => [
                'modelClass' => 'kmergen\eshop\paypal\models\PaypalRest',
                'label' => '<img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal Logo">',
                'pane' => '@kmergen/eshop/paypal/views/rest_pane',
                'paygate' => [
                    'class' => 'kmergen\eshop\paypal\PaygatePaypalRest',
                    'clientId' => 'AQIH9zYY-IqXG40tHZHq8VXwf4SMP3WhKubahnPxM-_-aBWcWVvVPGVWDroxWMNZNdUI5A7JQIgkui8z',
                    'clientSecret' => 'EDebRFO3vM_bRG6pHquCHB5VZaTT7TWczsr-edco3y0Ic4PDKYxfqUNw7ygR8wiKdNXjtpketf3KEQCz',
                    'returnUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/paypal-rest/success']),
                    'cancelUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/paypal-rest/cancel']),
                    'config' => [
                        'mode' => 'sandbox',
                    ],
                ],

            ],
            'stripe_card' => [
                'modelClass' => 'kmergen\eshop\stripe\models\Card',
                'label' => 'Credit Card '. Html::img('@web/themes/basic/img/creditcard_banner_h30.png'),
                'pane' => '@kmergen/eshop/stripe/views/card_pane',
                'paygate' => [
                    'class' => 'kmergen\eshop\stripe\PaygateStripe',
                    'publishKey' => 'pk_test_X9alOw25WC8wUGquMDlQctgS',
                    'secretKey' => 'sk_test_bEOZ97x0TN45lfKNorLmLUyD',
                ],
            ],
            'stripe_sepa' => [
                'modelClass' => 'kmergen\eshop\stripe\models\Sepa',
                'label' => 'Lastschrift ' . Html::img('@web/themes/basic/img/sepa_grey_h12.png'),
                'pane' => '@kmergen/eshop/stripe/views/sepa_pane',
                'paygate' => [
                    'class' => 'kmergen\eshop\stripe\PaygateStripe',
                    'publishKey' => 'pk_test_X9alOw25WC8wUGquMDlQctgS',
                    'secretKey' => 'sk_test_bEOZ97x0TN45lfKNorLmLUyD',
                ],
            ],
//            'paypal_ec_nvp' => [
//                'active' => false,
//                'modelClass' => 'kmergen\eshop\models\paypal\Paypal',
//                'label' => 'Paypal NVP <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal Logo">',
//                'paygate' => [
//                    'class' => 'kmergen\eshop\paypal\PaygateEcNvp',
//                    'mode' => 'sandbox',
//                    'apiUsername' => 'kmergen-test_api1.web.de',
//                    'apiPassword' => '1388849436',
//                    'apiSignature' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31AInuqmY-NrGhzuWh5EgtZdHPUzTe',
//                    'returnUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/paypal-ec-nvp/success']),
//                    'cancelUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/paypal-ec-nvp/cancel'])
//                ],
//            ],
//            'paypal_ec_soap' => [
//                'active' => false,
//                'modelClass' => 'kmergen\eshop\models\paypal\Paypal',
//                'label' => Yii::t('eshop', 'Paypal SOAP'),
//                'paygate' => [
//                    'class' => 'kmergen\eshop\paypal\PaygateEcSoap',
//                    'mode' => 'sandbox',
//                    'apiUsername' => 'kmergen-test_api1.web.de',
//                    'apiPassword' => '1388849436',
//                    'apiSignature' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31AInuqmY-NrGhzuWh5EgtZdHPUzTe',
//                    'returnUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/paypal-ec-soap/success']),
//                    'cancelUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/paypal-ec-soap/cancel'])
//                ],
//            ],
        ];
        $this->paymentMethods = ArrayHelper::merge($paymentMethodsDefault, $this->paymentMethods);
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['eshop*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => __DIR__ . '/messages',
            'sourceLanguage' => 'en',
        ];
    }

}
