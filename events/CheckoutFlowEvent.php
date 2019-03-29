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
 * PaymentEvent
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 *
 * This event is the base class for checkout events.
 */
class CheckoutFlowEvent extends Event
{
    /**
     * @var $initiator The controller class who initiated this flow.
     */
    public $initiator;

    /**
     * @var $redirectUrl string The url where the user should be redirected after checkout is complete
     */
    public $redirectUrl = '/eshop/checkout/complete';

    /**
     * @var $redirectParams array The params to apply on [[$redirectUrl]]
     */
    public $redirectParams = [];

    /**
     * @var bool Is a email message already sent.
     */
    public $messageSent = false;

    /**
     * @var $payment object The kmergen\eshop\models\Payment or null if it is not created yet.
     */
    public $payment;

    /**
     * @var $order object The kmergen\eshop\models\Order or null if it is not created yet.
     */
    public $order;

    public function getRedirectUrl() {
        return \array_merge((array)$this->redirectUrl, $this->redirectParams);
    }
}
