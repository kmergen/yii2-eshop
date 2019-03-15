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
 * CheckoutEvent
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 *
 * This event is the base class for checkout events.
 */
class CheckoutEvent extends Event
{
    /**
     * @var $action Which action is done on Checkout.
     */
    public $action;
}
