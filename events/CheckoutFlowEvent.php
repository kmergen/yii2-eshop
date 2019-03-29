<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\events;

use yii\base\Event;
use yii\helpers\Url;

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
     * @var $redirectUrl string|array The url where the user should be redirected after checkout is complete
     */
    public $redirectUrl = ['/eshop/checkout/complete'];

    /**
     * @var bool Is an email already sent.
     */
    public $emailSent = false;

    /**
     * @var array A flash message to show on the redirected page. e.g. ['success', 'Message to show']
     */
    public $flash;

    /**
     * @var $payment object The kmergen\eshop\models\Payment or null if it is not created yet.
     */
    public $payment;

    /**
     * @var $order object The kmergen\eshop\models\Order or null if it is not created yet.
     */
    public $order;

    public function getRedirectUrl()
    {
        return Url::to($this->redirectUrl);
    }
}
