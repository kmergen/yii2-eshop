<?php

namespace kmergen\eshop\helpers;

use Yii;
use kmergen\eshop\Module;
use yii\base\Component;
use yii\helpers\Html;
use kmergen\eshop\models\Order;

/**
 * Cart helper Class. This class handles  a cart for different webshops arround the application.
 */
class Cart extends Component
{
    const CART_ID = 'cart';
    const ORDER_ID = 'orderId';
    const LAST_URL = 'cartLastUrl';
    const STRIPE_INTENT = 'stripeIntent';
    const IS_CHECKOUT_CANCELED = 'isCheckoutCanceled';

    /** Triggered on item add */
    const EVENT_ITEM_ADD = 'addItem';
    /** Triggered on item add */
    const EVENT_ITEM_UPDATE = 'updateItem';
    /** Triggered on item add */
    const EVENT_BEFORE_ITEM_REMOVE = 'beforeRemoveItem';

    /**
     * Returns the cart from cart session, if not set null
     * @return mixed
     */
    public static function getCart()
    {
        return Yii::$app->session->get(self::CART_ID);
    }

    /**
     * Set cart to the cart session
     * @return void
     */
    public static function setCart($value)
    {
        Yii::$app->session->set(self::CART_ID, $value);
    }

    /**
     * Returns the order id from cart session, if not set null
     * @return mixed
     */
    public static function getOrderId()
    {
        return Yii::$app->session->get(self::ORDER_ID);
    }

    /**
     * Set order id to the cart session
     * @return void
     */
    public static function setOrderId($value)
    {
        Yii::$app->session->set(self::ORDER_ID, $value);
    }

    /**
     * Returns the last url from cart session, if not set null
     * @return mixed
     */
    public static function getLastUrl()
    {
        return Yii::$app->session->get(self::LAST_URL);
    }

    /**
     * Set last url to the cart session
     * @return void
     */
    public static function setLastUrl($value)
    {
        Yii::$app->session->set(self::LAST_URL, $value);
    }

    /**
     * Returns the stripe intent from cart session, if not set null
     * @return mixed
     */
    public static function getStripeIntent()
    {
        return Yii::$app->session->get(self::STRIPE_INTENT);
    }

    /**
     * Set stripe intent to the cart session
     * @return void
     */
    public static function setStripeIntent($value)
    {
        Yii::$app->session->set(self::STRIPE_INTENT, $value);
    }

    /**
     * Returns is checkout canceled from cart session, if not set null
     * @return mixed
     */
    public static function getCheckoutIsCanceled()
    {
        return Yii::$app->session->get(self::IS_CHECKOUT_CANCELED);
    }

    /**
     * Set is checkout canceled to the cart session (possible values are true or false)
     * @return void
     */
    public static function setCheckoutIsCanceled($value)
    {
        Yii::$app->session->set(self::IS_CHECKOUT_CANCELED, $value);
    }

    /**
     * Returns true if Cart is okay and false if not
     * @return boolean
     */
    public static function check()
    {
        $session = Yii::$app->session;
        // Check Order Id
        if (($orderId = self::getOrderId()) !== null) {
            if (self::getOrder($orderId) === null) {
                self::destroy();
                return false;
            }
        }

        return true;
    }

    /**
     * Delete the cart session variables
     */
    public static function destroy()
    {
        self::destroyCart();
        Yii::$app->session->remove(self::LAST_URL);
        Yii::$app->session->remove(self::STRIPE_INTENT);
        Yii::$app->session->remove(self::ORDER_ID);
        Yii::$app->session->remove(self::IS_CHECKOUT_CANCELED);
    }

    /**
     * Delete the cart session variable
     */
    public static function destroyCart()
    {
        Yii::$app->session->remove(self::CART_ID);
    }

    /**
     * Delete all items from the cart so the cart is empty
     */
    public static function clearCart()
    {
        Yii::$app->session->set(self::CART_ID, []);
    }

    /**
     * Get the order from Cart
     * @return mixed
     */
    public static function getOrder($id)
    {
        return Order::find()->with('orderItems')->where(['id' => $id])->one();
    }

    /**
     * Adds an item to the cart.
     * @param $id the unique article id
     * @param $qty integer the quantity of the article
     * @param $msg the message to display when the article is added to the cart
     */
    public static function addItem($id, $qty = 1, $msg = TRUE)
    {
        if (($cart = self::getCart()) === null) {
            $cart = [];
        }

        $default_qty = Yii::$app->db->createCommand("SELECT default_qty FROM eshop_article WHERE id=:id", [':id' => $id])->queryScalar();

        //If the article is not in the cart yet we add it
        if (!array_key_exists($id, $cart)) {
            $cart[$id] = $qty;

            if ($msg) {
                Yii::$app->session->setFlash('success', "Produkt wurde in Ihren Warenkorb gelegt.");
            }
        } //If the default_qty = 0 we do nothing because it was add the first time with the right qty 1
        elseif ($default_qty > 0) {
            $cart[$id] += $qty;
            if ($msg) {
                Yii::$app->session->setFlash('success', "Ihr Warenkorb wurde aktualisiert.");
            }
        }

        Yii::$app->session->set('cartLastUrl', Yii::$app->getRequest()->url);
        self::setCart($cart);
    }

    /**
     * Update a shopping cart item via ajax call
     * This ajax call is done from the checkout form cart pane
     * @param array items an array with the article id as key and the qty as value
     */
    public static function updateCartItems($items)
    {
        foreach ($items as $id => $qty) {
            if ($qty != 0) {
                $_SESSION[self::CART_ID][$id] = $qty;
            } else {
                unset($_SESSION[self::CART_ID][$id]);
            }
        }
    }

    /**
     * Returns the total price from the total of an order or if no order exist from the cart items.
     * If the cart is empty or not exists it returns 0.
     * @return mixed float integer
     */
    public static function getTotal()
    {
        $total = 0;

        if (empty(self::getCart())) {
            return $total;
        } else {
            $cart = self::getCart();
        }

        if (($orderId = self::getOrderId()) !== null) {
            $total = Yii::$app->db->createCommand('SELECT total FROM eshop_order WHERE id=:id', [':id' => $orderId])->queryScalar();
        } else {
            $article_ids = implode(',', array_keys($cart));
            if (empty($article_ids)) {
                return $total;
            }
            $rows = Yii::$app->db->createCommand("SELECT * FROM eshop_article WHERE id IN($article_ids)")->queryAll();
            foreach ($rows as $row) {
                $row['qty'] = $cart[$row['id']];
                $row['total_price'] = $row['qty'] * $row['sell_price'];
                $items[] = $row;
                $total += $row['total_price'];
            }
        }
        return $total;
    }

    /**
     * Update the shopping cart.
     * @param array items an array in the form array('id'=>'qty')
     *
     */
    public static function update($items)
    {
        foreach ($items as $id => $qty) {
            if ($qty != 0) {
                $_SESSION[self::CART_ID][$id] = $qty;
            } else {
                unset($_SESSION[self::CART_ID][$id]);
            }
        }
    }

    /**
     * Return the items in a shopping cart.
     */
    public static function getCartContent()
    {
        if (empty(self::getCart())) {
            return [];
        } else {
            $cart = self::getCart();
        }

        $article_ids = implode(',', array_keys($cart));

        if (empty($article_ids)) {
            return [];
        }

        $sql = "SELECT article.*, article_category.shipping
  FROM eshop_article AS article INNER JOIN eshop_article_category AS article_category ON (article.category_id = article_category.id) WHERE article.id IN($article_ids)";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        $items = [];
        $total = 0;
        $shipping = false;
        foreach ($rows as $row) {
            $row['qty'] = $cart[$row['id']];
            $row['total_price'] = $row['qty'] * $row['sell_price'];
            $items[] = $row;
            $total += $row['total_price'];
            if ($row['shipping']) {
                $shipping = true;
            }
        }
        return ['items' => $items, 'total' => $total, 'shipping' => $shipping];
    }

    /**
     * This function is normally called via ajax when we change the qty checkbox or the qty textfield
     * @return the rendered html
     */
    public static function renderCartPane()
    {
        $cartContent = static::getCartContent();
        $items = $cartContent['items'];
        $total = $cartContent['total'];

        $header = '<table class="tbl-cart-pane">';
        $header .= '<table class="tbl-cart-pane">';
        $header .= '<thead><tr>';
        $header .= '<th>' . Yii::t('eshop', 'Quantity') . '</th>';
        $header .= '<th>' . Yii::t('eshop', 'ArticleOld') . '</th>';
        $header .= '<th>' . Yii::t('eshop', 'Price') . '</th>';
        $header .= '<th>' . Yii::t('eshop', 'Price') . '</th>';
        $header .= '</tr></thead>';

        $body = '<tbody>';

        foreach ($items as $item) {
            $trClass = $item['qty'] < 1 ? 'class="no-qty" ' : '';
            $body .= "<tr $trClass><td>";

            if ($item['selectable']) {
                if ($item['qty'] < 2) {
                    $body .= Html::checkBox('articles[' . $item['id'] . ']', ($item['qty'] < 1) ? false : true, ['id' => 'article_' . $item['id'], 'class' => 'article-qty checkout-input', 'value' => $item['qty'] < 1 ? 0 : 1]);
                } else {
                    $body .= Html::textField('articles[' . $item['id'] . ']', $item['qty'], ['id' => 'article_' . $item['id'], 'class' => 'article-qty checkout-input', 'size' => 5]);
                }
            } else {
                $body .= Html::hiddenInput('articles[' . $item['id'] . ']', $item['qty']);
                $body .= "<span>$item[qty]</span>";
            }
            $body .= '</td>';
            $body .= '<td>' . Html::encode($item['title']) . '</td>';
            $body .= '<td>' . Helper::formatCurrency(Html::encode($item['sell_price'])) . '</td>';
            $body .= '<td>' . Helper::formatCurrency(Html::encode($item['total_price'])) . '</td>';
            $body .= '</tr>';
        }

        $body .= '<tr>';
        $body .= '<td class="td-review-total-label" colspan="3">' . Module::t('Total Price') . '</td>';
        $body .= '<td class="td-review-total-price" colspan="3"><strong>' . Helper::formatCurrency($total) . '</strong></td>';
        $body .= '</tr></tbody></table>';

        $html = $header . $body;

        return $html;
    }

}
