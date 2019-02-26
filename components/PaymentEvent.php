<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\components;

use yii\base\Event;

/**
 * PaymentEvent
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 *
 * This event is the base class for payment events.
 */
class PaymentEvent extends Event
{
    /**
     * @var $payment array This array contains payment information that can load by Payment Model.
     */
    public $payment;

    /**
     * @var $paymentStatus string Contains the actual payment status.
     */
    public $paymentStatus;
}
