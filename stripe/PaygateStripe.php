<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\stripe;


use yii\base\BaseObject;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\base\Exception;
use Yii;

class PaygateStripe extends BaseObject
{
    public $publishKey;
    public $secretKey;
    public $currency = 'eur';

    function init()
    {

    }

    public function execute($order, $customer, $params = null)
    {
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        try {
            \Stripe\Stripe::setApiKey($this->secretKey);

            // Token is created using Checkout or Elements!
            // Get the payment token ID submitted by the form:
            $token = $_POST['stripeToken'];
            $charge = \Stripe\Charge::create([
                'amount' => ($order->total * 100), // eurocent
                'currency' => $this->currency,
                'description' => 'Example charge form Hundekauf.de',
                'source' => $token,
            ]);
        } catch (Exception $ex) {
            Yii::error([
                'Error Message' => $ex->getMessage(),
            ], 'stripe');
        }

        return true;
    }

}
