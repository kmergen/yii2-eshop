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


}
