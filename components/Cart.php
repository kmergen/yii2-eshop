<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\components;

use kmergen\eshop\models\Order;
use yii\base\Component;
use yii\base\Exception;
use Yii;

/**
 * Cart
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 */
class Cart extends Component
{
    public function __clone()
    {

    }

    public function __construct()
    {
        return null;
    }

    private static $cart;

    /**
     * Return the existing Cart or return a new one.
     * A Cart in Eshop module is an instance of kmergen\eshop\models\Order with Order::STATUS_CART
     * @return object;
     */
    public static function getCart()
    {
        if (static::$cart === null) {
            if (($cart = self::getCurrentCart()) === null) {
                $cart = Yii::createObject([
                    'class' => Order::class,
                    'status' => Order::STATUS_CART,
                    'checkout_status' => Order::STATUS_CART,
                    'payment_status' => Order::STATUS_CART,
                    'shipping_status' => Order::STATUS_CART,
                ]);
                $cart->save();
                Yii::$app->session->set('eshop.cart', $cart->id);
                static::$cart = $cart;
            } else {
                static::$cart = $cart;
            }
        }
        return static::$cart;
    }

    /**
     * Return null or the current Cart kmergen\eshop\models\Order
     * @return mixed
     * @throws yii\base\Exception
     */
    public static function getCurrentCart()
    {
        if (($orderId = Yii::$app->session->get('eshop.cart')) === null) {
            return null;
        } else {
            if (($order = Order::find()->with('items')->where(['id' => $orderId])->one()) !== null) {
                return $order;
            } else {
                $msg = 'Order Id is set in session, but cannot get order Model. Order Id: ' . $orderId;
                Yii::error($msg, __METHOD__);
                throw new Exception($msg);
            }
        }
    }
}
