<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\stripe;


use yii\base\Component;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\base\Exception;
use Yii;
use kmergen\eshop\components\PaymentEvent;
use kmergen\eshop\models\PaymentStatus;
use kmergen\eshop\helpers\Cart;


class PaygateStripe extends Component
{
    public $publishKey;
    public $secretKey;
    public $currency = 'eur';

    private $intentIdSessionKey = 'stripeIntentId';

    const EVENT_PAYMENT_DONE = 'payment_done';

    /**
     * Stripe payment methods are handled on client side, means they send a request to stripe with payment information and stripe
     * response in a promise if payment succeed or failed. To fullfill the order on server side we must include webhooks from stripe.
     * @see https://stripe.com/docs/payments/payment-intents
     * @param kmergen\eshop\models\Order
     * @return \Stripe\ApiResource
     */
    public function getIntent() {
        if (Yii::$app->session->get($this->intentIdSessionKey) === null) {
            return $this->createIntent();
        } else {
            return $this->retrieveIntent();
        }
    }

    /**
     * Create a stripe Intent
     * @return \Stripe\ApiResource
     */
    public function createIntent()
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $intent = \Stripe\PaymentIntent::create([
            "amount" => (Cart::getTotal() * 100), // eurocent
            "currency" => $this->currency,
            "payment_method_types" => ["card"],
        ]);
        Yii::$app->session->set($this->intentIdSessionKey, $intent->id);
        Yii::$app->session->remove($this->intentIdSessionKey); // only for testing

        return $intent;
    }

    /**
     * Retrieve the stripe Intent
     * @return \Stripe\ApiResource
     */
    public function retrieveIntent()
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        return \Stripe\PaymentIntent::retrieve(Yii::$app->session->get($this->intentIdSessionKey));
    }

    /**
     * @param $order
     * @param $customer
     * @param null $params
     */
    public function execute($order, $customer, $params = null)
    {
        $metadata = [
            'order_id' => $order->id,
        ];

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
                'metadata' => $metadata
            ]);
            $this->buildPaymentInfo($charge);
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err = $body['error'];
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
        } catch (\Stripe\Error\Base $e) {
            $e_msg = $e->getMessage();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            Yii::error($e->getMessage(), 'eshop');
        }
    }

    protected function buildPaymentInfo($ch)
    {
        $info = [];
        $info['Payment']['payment_provider'] = 'Stripe';
        $info['Payment']['transaction_id'] = $ch->id;
        $info['Payment']['order_id'] = $ch->metadata->order_id;
        $info['Payment']['payment_method'] = 'stripe_card';
        $info['Payment']['data'] = $ch;

        if ($ch->status === 'succeeded') {
            $info['Payment']['status'] = PaymentStatus::COMPLETE;
        } else {
            $info['Payment']['status'] = PaymentStatus::PENDING;
        }

        $event = new PaymentEvent();
        $event->payment = $info;
        $this->trigger(self::EVENT_PAYMENT_DONE, $event);
    }

}
