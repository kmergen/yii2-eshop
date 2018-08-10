<?php

namespace app\modules\eshop\helpers;

use Yii;
use app\modules\eshop\Module;
use app\helpers\Helper;
use yii\helpers\Html;

/**
 * Cart helper Class. This class handles  a cart for different webshops arround the application.
 */
class Cart
{
    const CART_ID = 'cart';

    /**
     * Delete the cart session variables
     */
    public static function destroy()
    {
        Yii::$app->session->remove(self::CART_ID);
        Yii::$app->session->remove('cartLastUrl');
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
     * Adds an item to the cart.
     * @param $id the unique article id
     * @param $qty integer the quantity of the article
     * @param $msg the message to display when the article is added to the cart
     */
    public static function addItem($id, $qty = 1, $msg = TRUE)
    {
        if (($cart = Yii::$app->session->get(self::CART_ID)) === null) {
            $cart = [];
        } 
        
        $default_qty = Yii::$app->db->createCommand("SELECT default_qty FROM eshop_article WHERE id=:id", [':id' => $id])->queryScalar();

        //If the article is not in the cart yet we add it
        if (!array_key_exists($id, $cart)) {
            $cart[$id] = $qty;

            if ($msg) {
                Yii::$app->session->setFlash('success', "Produkt wurde in Ihren Warenkorb gelegt.");
            }
        }
        //If the default_qty = 0 we do nothing because it was add the first time with the right qty 1 
        elseif ($default_qty > 0) {
            $cart[$id] += $qty;
            if ($msg) {
                Yii::$app->session->setFlash('success', "Ihr Warenkorb wurde aktualisiert.");
            }
        }

        Yii::$app->session->set('cartLastUrl', Yii::$app->getRequest()->url);
        Yii::$app->session->set(self::CART_ID, $cart);
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
                $_SESSION[self::CART_ID][$id] = $item['qty'];
            } else {
                unset($_SESSION[self::CART_ID][$id]);
            }
        }
    }

    /**
     * Returns the total price from the items in cart
     */
    public static function getTotalPrice()
    {
        $total = 0;
        foreach ($items as $v) {
            $total += ($v['price'] * $v['qty']);
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
     *
     */
    public static function getCartContent()
    {

        if (empty($_SESSION[self::CART_ID])) {
            return [];
        }else {
            $cart = $_SESSION[self::CART_ID];
        }


        $article_ids = implode(',', array_keys($cart));
        $sql = "SELECT * FROM eshop_article WHERE id IN($article_ids)";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        $items = [];
        $total = 0;
        foreach ($rows as $row) {
            $row['qty'] = $cart[$row['id']];
            $row['total_price'] = $row['qty'] * $row['sell_price'];
            $items[] = $row;
            $total+=$row['total_price'];
        }
        return ['items' => $items, 'total' => $total];
    }

    /**
     * This function is normally called via ajax when we change the qty checkbox or the qty textfield
     * @return the rendered html
     */
    public static function renderCartPane($cartContent)
    {
        $items = $cartContent['items'];
        $total = $cartContent['total'];

        $header = '<table class="tbl-cart-pane">';
        $header.='<table class="tbl-cart-pane">';
        $header.='<thead><tr>';
        $header.='<th>' . Module::t('Quantity') . '</th>';
        $header.='<th>' . Module::t('Article') . '</th>';
        $header.='<th>' . Module::t('Price') . '</th>';
        $header.='<th>' . Module::t('Price') . '</th>';
        $header.='</tr></thead>';

        $body = '<tbody>';

        foreach ($items as $item) {
            $trClass = $item['qty'] < 1 ? 'class="no-qty" ' : '';
            $body.="<tr $trClass><td>";

            if ($item['selectable']) {
                if ($item['qty'] < 2) {
                    $body.=Html::checkBox('articles[' . $item['id'] . ']', ($item['qty'] < 1) ? false : true, ['id' => 'article_' . $item['id'], 'class' => 'article-qty checkout-input', 'value' => $item['qty'] < 1 ? 0 : 1]);
                } else {
                    $body.=Html::textField('articles[' . $item['id'] . ']', $item['qty'], ['id' => 'article_' . $item['id'], 'class' => 'article-qty checkout-input', 'size' => 5]);
                }
            } else {
                $body.=Html::hiddenInput('articles[' . $item['id'] . ']', $item['qty']);
                $body.="<span>$item[qty]</span>";
            }
            $body.='</td>';
            $body.='<td>' . Html::encode($item['title']) . '</td>';
            $body.='<td>' . Helper::formatCurrency(Html::encode($item['sell_price'])) . '</td>';
            $body.='<td>' . Helper::formatCurrency(Html::encode($item['total_price'])) . '</td>';
            $body.='</tr>';
        }

        $body.='<tr>';
        $body.='<td class="td-review-total-label" colspan="3">' . Module::t('Total Price') . '</td>';
        $body.='<td class="td-review-total-price" colspan="3"><strong>' . Helper::formatCurrency($total) . '</strong></td>';
        $body.='</tr></tbody></table>';

        $html = $header . $body;

        return $html;
    }

}
