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
 * WebhookEventInterface
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 *
 * This event fired by incomming a stripe Webhook
 * @see https://stripe.com/docs/webhooks.
 */
interface WebhookEventInterface
{
    const EVENT_STRIPE_WEBHOOK = 'stripeWebhook';
}
