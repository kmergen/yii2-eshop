<?php

namespace kmergen\eshop;

use Yii;

class Module extends \yii\base\Module
{
    public $paymentMethods = [
        'Debit' => ['paygateClass' => 'app\modules\eshop\components\novalnet\Debit', 'label' => 'Debit'],
        'PaypalBasic' => ['paygateClass' => 'app\modules\eshop\components\paypal\PaypalBasic', 'label' => 'Paypal'],
        'Transfer' => ['paygateClass' => 'app\modules\eshop\components\novalnet\Transfer', 'label' => 'Transfer'],
    ];

    public $currencySign = '€';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
        //Yii::$classMap[$this->paygateClass] = '@app/modules/eshop/components/novalnet/Novalnet.php';
       $lang = Yii::$app->language;

    }


    /**
     * Return the paygate for the specific payment method
     * @param string paymentMethod
     */
    public function getPaygate($paymentMethod)
    {
        return $this->paymentMethods[$paymentMethod]['paygate'];
    }

    /**
     * Return the allowed payment methods for the specific cart
     */
    public function listPaymentMethods()
    {
        $items = [];
        foreach ($this->paymentMethods as $key => $v) {
            $items[$key] = self::t($v['label']);
        }
        return $items;
    }

    public function beforeControllerAction($controller, $action)
    {
        if (parent::beforeControllerAction($controller, $action)) {
            return true;
        } else {
            return false;
        }
    }

}
