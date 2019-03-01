<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\stripe;

use yii\base\Event;

/**
 * Stripe Event
 * All stripe events extents from this class, because we will send additional data
 * to the event handler
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 *
 */
class StripeEvent extends Event
{
    /**
     * @var The stripe data Object.
     */
    public $stripeData;
}
