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

    /**
     * Adds an item to the cart.
     * @param $id the unique product id
     * @param $qty integer the quantity of the product
     * @param $msg the message to display when the product is added to the cart
     */
    public static function addItem($id, $qty = 1, $msg = TRUE)
    {

    }

    /**
     * Update a shopping cart item via ajax call
     * This ajax call is done from the checkout form cart pane
     * @param array items an array with the product id as key and the qty as value
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
            $product_ids = implode(',', array_keys($cart));
            if (empty($product_ids)) {
                return $total;
            }
            $rows = Yii::$app->db->createCommand("SELECT * FROM eshop_product WHERE id IN($product_ids)")->queryAll();
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

        $product_ids = implode(',', array_keys($cart));

        if (empty($product_ids)) {
            return [];
        }

        $sql = "SELECT product.*, product_category.shipping
  FROM eshop_product AS product INNER JOIN eshop_product_category AS product_category ON (product.category_id = product_category.id) WHERE product.id IN($product_ids)";
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
                    $body .= Html::checkBox('products[' . $item['id'] . ']', ($item['qty'] < 1) ? false : true, ['id' => 'product_' . $item['id'], 'class' => 'product-qty checkout-input', 'value' => $item['qty'] < 1 ? 0 : 1]);
                } else {
                    $body .= Html::textField('products[' . $item['id'] . ']', $item['qty'], ['id' => 'product_' . $item['id'], 'class' => 'product-qty checkout-input', 'size' => 5]);
                }
            } else {
                $body .= Html::hiddenInput('products[' . $item['id'] . ']', $item['qty']);
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
