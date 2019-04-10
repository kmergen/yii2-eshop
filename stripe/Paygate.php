<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\stripe;

use yii\base\Component;
use yii\base\Exception;
use Yii;
use kmergen\eshop\components\PaymentEvent;
use kmergen\eshop\models\PaymentStatus;
use kmergen\eshop\models\Cart;

class Paygate extends Component
{
    public $publishKey;
    public $secretKey;
    public $currency = 'eur';
    public $intentIdKey = 'stripeIntentId';

    /**
     * Stripe payment methods are handled on client side, means they send a request to stripe with payment information and stripe
     * response in a promise if payment succeed or failed. To fullfill the order on server side we must include webhooks from stripe.
     * @see https://stripe.com/docs/payments/payment-intents
     * @param kmergen\eshop\models\Order
     * @return \Stripe\ApiResource
     */
    public function getIntent()
    {
        $cart = Cart::getCurrentCart();
        $id = isset($cart->metadata[$this->intentIdKey]) ? $cart->metadata[$this->intentIdKey] : null;
        if ($id === null) {
            return $this->createIntent($cart);
        } else {
            $intent = $this->retrieveIntent($id);
            if ($intent->status === 'succeeded' || $intent->status === 'canceled') {
                return $this->createIntent();
            } else {
                return $intent;
            }
        }
    }

    /**
     * Create a stripe PaymentIntent
     * @return \Stripe\ApiResource
     */
    public function createIntent($cart)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $intent = \Stripe\PaymentIntent::create([
            "amount" => ($cart->total * 100), // eurocent
            "currency" => $this->currency,
            "payment_method_types" => ["card"],
            'metadata' => [
                'cart_id' => $cart->id,
            ]
        ], [
            'idempotency_key' => Yii::$app->getSecurity()->generateRandomString()
        ]);
        $metadata = $cart->metadata;
        $metadata[$this->intentIdKey] = $intent->id;
        $cart->metadata = $metadata;
        $cart->update(false);
        return $intent;
    }

    /**
     * Retrieve Payment Intent
     * @return \Stripe\ApiResource
     */
    public function retrieveIntent($id)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        return \Stripe\PaymentIntent::retrieve($id);
    }

    /**
     * Update Payment Intent
     * @param $id string The Payment Intent Id
     * @param $data array The data to update
     * @return \Stripe\ApiResource
     */
    public function updateIntent($id, $data)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        return \Stripe\PaymentIntent::update($id, $data);
    }
}
