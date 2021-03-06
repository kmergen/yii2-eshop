<?php

namespace kmergen\eshop\models;

use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "eshop_cart".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $invoice_address_id
 * @property int $shipping_address_id
 * @property int $total
 *
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $metadata
 *
 * @property Address $invoiceAddress
 * @property Address $shippingAddress
 * @property Customer $customer
 */
class Cart extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 'new';
    const STATUS_COMPLETE = 'complete';
    const STATUS_ABANDONED = 'abandoned';
    const CART_SESSION_KEY = 'eshop.cart';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_cart';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()')
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'invoice_address_id', 'shipping_address_id'], 'integer'],
            [['total'], 'number'],
            [['status'], 'string', 'max' => 70],
            [['invoice_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::class, 'targetAttribute' => ['invoice_address_id' => 'id']],
            [['shipping_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::class, 'targetAttribute' => ['shipping_address_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            ['metadata', 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'customer_id' => Yii::t('eshop', 'Customer ID'),
            'invoice_address_id' => Yii::t('eshop', 'Invoice Address ID'),
            'shipping_address_id' => Yii::t('eshop', 'Shipping Address ID'),
            'status' => Yii::t('eshop', 'Status'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
    }

    /**
     * Return the existing Cart or create a new one and return it.
     * @return object
     */
    public static function getCart()
    {
        if (($cart = self::getCurrentCart()) === null) {
            $cart = Yii::createObject([
                'class' => static::class,
                'total' => 0,
                'status' => Cart::STATUS_NEW
            ]);
            $cart->save(false);
            Yii::$app->session->set(self::CART_SESSION_KEY, $cart->id);
        }
        return $cart;
    }

    /**
     * Return null or the current Cart.
     * If the cart is complete (has payment and order) this function return null, because can not use cart that is complete.
     * @return null|object
     * @throws yii\base\Exception
     */
    public static function getCurrentCart()
    {
        if (($cartId = Yii::$app->session->get(self::CART_SESSION_KEY)) === null) {
            return null;
        } else {
            if (($cart = static::findOne($cartId)) !== null) {
                if ($cart->status === self::STATUS_COMPLETE) {
                    static::removeCartFromSession();
                    return null;
                } else {
                    return $cart;
                }
            } else {
                $msg = 'Cart Id is set in session, but cannot get Cart Model for Cart Id: ' . $cartId;
                Yii::error($msg, __METHOD__);
                static::removeCartFromSession();
                return null;
            }
        }
    }

    /**
     * Delete the current Cart from Session if it exists.
     * @return void
     */
    public static function removeCartFromSession()
    {
        Yii::$app->session->remove(self::CART_SESSION_KEY);
    }

    /**
     * Delete all items from the current Cart.
     * @return void
     */
    public function clearCart()
    {
        if (static::getCurrentCart() !== null) {
            if (!empty($this->items)) {
                foreach ($this->items as $item) {
                    $this->unlink('items', $item, true);
                }
                $this->updateAttributes(['total' => 0]);
            }
        }
    }

    /**
     * Return kmergen\eshop\models\Product
     * @param integer $id The Product Id
     * @return object kmergen\eshop\models\Product
     * @throws Exception
     */
    protected function getProduct($id)
    {
        if (($product = Product::findOne($id)) === null) {
            $errorMessage = 'Cannot add item to Cart. Product with Id: ' . $id . ' not found.';
            Yii::error($errorMessage, __METHOD__);
            throw new Exception($errorMessage);
        }
        return $product;
    }

    /**
     * @param $id integer kmergen\eshop\Product id
     * @param int $qty The quantity to add from this product
     * @param bool $msg Show flash message or not. You can set this to false for e.g. if add item programmatically to Cart
     * @throws Exception
     */
    public function addItem($id, $qty = 1, $msg = true)
    {
        if ($this->getItem($id) === null) {
            if ($qty > 0) {
                $product = $this->getProduct($id);
                $item = new CartItem();
                $item->product_id = $product->id;
                $item->title = $product->title;
                $item->sku = $product->sku;
                $item->qty = ($product->max_qty >= $qty) ? $qty : $product->max_qty;
                $item->sell_price = $product->sell_price;
                $this->link('items', $item);
                $this->recalculateCart();
            }
        } else {
            $this->updateItem($id, $qty);
        }
    }

    /**
     * Update the qty of a Cart Item
     * @param $id integer kmergen\eshop\Product id
     * @param $qty integer Quantity
     * @throws Exception
     */
    public function updateItem($id, $qty)
    {
        if (($item = $this->getItem($id)) !== null) {
            if ($qty == 0) {
                $this->unlink('items', $item, true);
            } elseif ($item->qty != $qty) {
                $oldItemQty = $item->qty;
                $product = $this->getProduct($id);
                $item->qty = ($product->max_qty >= $qty) ? $qty : $product->max_qty;
                if ($oldItemQty != $item->qty) {
                    $item->updateAttributes(['qty' => $item->qty]);
                }
            }
            $this->recalculateCart();
        }
    }

    /**
     * Remove Item from Cart
     * @param $id integer kmergen\eshop\Product id
     */
    public function removeItem($id)
    {
        if (($item = $this->getItem($id)) !== null) {
            $this->unlink('items', $item, true);
            $this->recalculateCart();
        }
    }

    /**
     * Recalculate the cart total value after cart update
     */
    public function recalculateCart()
    {
        $price = 0;
        foreach ($this->items as $item) {
            $price += $item->sell_price * $item->qty;
        }
        $this->updateAttributes(['total' => $price]);
    }

    /**
     * Return kmergen\eshop\models\CartItem Object or null if it not exist
     * @param $id integer kmergen\eshop\models\Product Id
     * @return mixed
     */
    public function getItem($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    /**
     * Return true if physical products are in Cart, otherwise false
     * @return boolean
     */
    public function needShipping()
    {
        $productIds = array_keys($this->items);
        $rows = Product::find()->asArray()->where(['is_physical' => 1])->all();
        return empty($rows) ? false : true;
    }

    /**
     * Handle the yii\web\User::EVENT_LOGOUT event
     * @param $event
     */
    public static function handleUserBeforeLogout($event)
    {
        if (($cart = static::getCurrentCart()) !== null) {
            if ($cart->status === self::STATUS_NEW) {
                $cart->updateAttributes(['status' => self::STATUS_ABANDONED]);
            }
        }
    }

    public function afterFind()
    {
        if (!empty($this->metadata)) {
            $this->metadata = Json::decode($this->metadata);
        }
        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (!empty($this->metadata)) {
            $this->metadata = Json::encode($this->metadata);
        }
        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoiceAddress()
    {
        return $this->hasOne(Address::class, ['id' => 'invoice_address_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShippingAddress()
    {
        return $this->hasOne(Address::class, ['id' => 'shipping_address_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(CartItem::class, ['cart_id' => 'id'])->indexBy('product_id');
    }
}
