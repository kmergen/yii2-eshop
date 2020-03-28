<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\events;

use yii\base\Event;

/**
 * Stripe Payment Intent Event
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 *
 * This event is the base class for Stripe Payment Intent Events.
 */
class StripePaymentIntentEvent extends Event
{
    const EVENT_STRIPE_PAYMENT_INTENT_SUCCEED = 'stripePaymentIntentSucceed';
    const EVENT_STRIPE_PAYMENT_INTENT_FAILED = 'stripePaymentIntentFailed';

    /**
     * @var string The Stripe Payment Intent Webhook
     */
    public $webhook;

    /**
     * @var object The Stripe Payment Intent
     */
    public $intent;

    /**
     * @var bool Is an email already sent.
     */
    public $emailSent = false;

    /**
     * @var $payment object The kmergen\eshop\models\Payment or null if it is not created yet.
     */
    public $payment;

    /**
     * @var $order object The kmergen\eshop\models\Order or null if it is not created yet.
     */
    public $order;


}
