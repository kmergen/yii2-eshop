<?php

namespace kmergen\eshop;

use Yii;
use yii\i18n\PhpMessageSource;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class Module extends \yii\base\Module
{
    public $shopName = 'Eshop-Testshop';

    public $shopEmail = 'eshop@example.com';

    public $termsOfServiceUrl = ['/site/agb'];

    public $revocationUrl = ['/site/widerruf'];

    public $dataProtectionUrl = ['/site/datenschutz'];

    public $defaultRoute = 'dashboard';

    public $paymentMethods = [];

    public $currencySign = '€';

    public $classEventHandler = [];

    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                'class' => admin\Module::class,
            ],
        ];

        $this->registerTranslations();

        $paymentMethodsDefault = [
            'paypal_rest' => [
                'enabled' => true,
                'modelClass' => 'kmergen\eshop\paypal\models\PaypalRest',
                'labelText' => '<img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal Logo">',
                'labelAsset' => false,
                'paneurl' => ['/eshop/paypal-rest/pane'],
                'paygate' => [
                    'class' => 'kmergen\eshop\paypal\PaygatePaypalRest',
                    'clientId' => 'AQIH9zYY-IqXG40tHZHq8VXwf4SMP3WhKubahnPxM-_-aBWcWVvVPGVWDroxWMNZNdUI5A7JQIgkui8z',
                    'clientSecret' => 'EDebRFO3vM_bRG6pHquCHB5VZaTT7TWczsr-edco3y0Ic4PDKYxfqUNw7ygR8wiKdNXjtpketf3KEQCz',
                    'returnUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/checkout/paypal-approval']),
                    'cancelUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/eshop/checkout/paypal-cancel']),
                    'config' => [
                        'mode' => 'sandbox',
                    ],
                ],

            ],
            'stripe_card' => [
                'enabled' => true,
                'modelClass' => 'kmergen\eshop\stripe\models\Card',
                'labelText' => Yii::t('eshop', 'Credit Card'),
                'labelAsset' => 'images/credit_card_banner.png',
                'paneurl' => ['/eshop/stripe/card-pane'],
                'paygate' => [
                    'class' => 'kmergen\eshop\stripe\Paygate',
                    'publishKey' => 'pk_test_X9alOw25WC8wUGquMDlQctgS',
                    'secretKey' => 'sk_test_bEOZ97x0TN45lfKNorLmLUyD',
                ],
            ],
            'stripe_sepa' => [
                'enabled' => false,
                'modelClass' => 'kmergen\eshop\stripe\models\Sepa',
                'labelText' => Yii::t('eshop', 'Direct Debit'),
                'labelAsset' => 'images/sepa_grey_h12.png',
                'paneurl' => ['/eshop/stripe/sepa-pane'],
                'paygate' => [
                    'class' => 'kmergen\eshop\stripe\Paygate',
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
//                    'returnUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/shop/paypal-ec-nvp/success']),
//                    'cancelUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/shop/paypal-ec-nvp/cancel'])
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
//                    'returnUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/shop/paypal-ec-soap/success']),
//                    'cancelUrl' => Yii::$app->urlManager->createAbsoluteUrl(['/shop/paypal-ec-soap/cancel'])
//                ],
//            ],
        ];
        $this->paymentMethods = ArrayHelper::merge($paymentMethodsDefault, $this->paymentMethods);
        $this->registerEventHandler();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['eshop*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => __DIR__ . '/messages',
            'sourceLanguage' => 'en',
        ];
    }

    private function registerEventHandler()
    {
        $eventHandler = ArrayHelper::merge([
            'userLogout' => [
                'class' => \yii\web\User::class,
                'event' => \yii\web\User::EVENT_BEFORE_LOGOUT,
                'callable' => [models\Cart::class, 'handleUserBeforeLogout']
            ],
//            'paymentInsert' => [
//                'class' => controllers\CheckoutController::class,
//                'event' => controllers\CheckoutController::EVENT_CHECKOUT_PAYMENT_INSERT,
//                'callable' => [models\Order::class, 'checkoutPaymentInsert']
//            ],
//            'orderInsert' => [
//                'class' => models\Order::class,
//                'event' => models\Order::EVENT_ORDER_INSERT,
//                'callable' => [controllers\CheckoutController::class, 'handleOrderInsert']
//            ],
        ], $this->classEventHandler);

        foreach ($eventHandler as $handler) {
            \yii\base\Event::on($handler['class'], $handler['event'], $handler['callable']);
        }
    }

}
