<?php

namespace kmergen\eshop\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\base\Exception;

/**
 * This is the model class for table "eshop_order".
 *
 * @property int $id Primary key: the order ID.
 * @property int $customer_id
 * @property string $status The order status.
 * @property string $total
 * @property int $invoice_address_id
 * @property string $ip Host IP address of the person paying for the order.
 * @property string $notes Order notes
 * @property string $created_at
 * @property string $updated_at
 * @property Customer $customer
 * @property Address $invoiceAddress
 * @property OrderItem[] $eshopOrderItems
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_CART = 'cart';
    const STATUS_NEW = 'new';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETE = 'complete';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_order';
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
            [['status', 'checkout_status', 'payment_status', 'shipping_status'], 'required'],
            [['customer_id', 'invoice_address_id'], 'integer'],
            [['total'], 'number'],
            [['notes'], 'string'],
            [['status'], 'string', 'max' => 32],
            [['ip'], 'string', 'max' => 255],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['invoice_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::class, 'targetAttribute' => ['invoice_address_id' => 'id']],
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
            'status' => Yii::t('eshop', 'Status'),
            'total' => Yii::t('eshop', 'Total'),
            'invoice_address_id' => Yii::t('eshop', 'Invoice Address ID'),
            'ip' => Yii::t('eshop', 'Ip'),
            'notes' => Yii::t('eshop', 'Comment'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
    }

    /**
     * Return the existing Cart or return a new one.
     * A Cart in Eshop module is an instance of kmergen\eshop\models\Order with Order::STATUS_CART
     * @return object;
     */
    public static function getCart()
    {
        if (($cart = self::getCurrentCart()) === null) {
            $cart = Yii::createObject([
                'class' => static::class,
                'status' => self::STATUS_CART,
                'checkout_status' => self::STATUS_CART,
                'payment_status' => self::STATUS_CART,
                'shipping_status' => self::STATUS_CART,
                'total' => 0,
            ]);
            $cart->save();
            Yii::$app->session->set('eshop.cart', $cart->id);
        }
        return $cart;
    }

    /**
     * Return null or the current Cart kmergen\eshop\models\Order
     * @return mixed
     * @throws yii\base\Exception
     */
    public static function getCurrentCart()
    {
        if (($cartId = Yii::$app->session->get('eshop.cart')) === null) {
            return null;
        } else {
            //if (($order = static::find()->with('items')->where(['id' => $orderId])->one()) !== null) {
            if (($cart = static::findOne($cartId)) !== null) {
                return $cart;
            } else {
                $msg = 'Order Id is set in session, but cannot get order Model. Order Id: ' . $cartId;
                Yii::error($msg, __METHOD__);
                throw new Exception($msg);
            }
        }
    }

    /**
     * Delete the current Cart if it exists.
     * @return void
     */
    public function deleteCart()
    {
        if (static::getCurrentCart() !== null) {
            $this->delete();
            Yii::$app->session->remove('eshop.cart');
        }
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
        if (($item = $this->getItem($id)) === null) {
            if ($qty > 0) {
                $product = $this->getProduct($id);
                $item = new OrderItem();
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
     * Return kmergen\eshop\models\OrderItem Object or null if it not exist
     * @param $id integer kmergen\eshop\models\Product Id
     * @return mixed
     */
    public function getItem($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    /**
     * Return true if products in Cart that needs shipping, otherwise false
     * @return boolean
     */
    public function needShipping()
    {
        $productIds = array_keys($this->items);
        $rows = Product::find()
            ->asArray()
            ->innerJoin('eshop_product_category', '`eshop_product`.`category_id` = `eshop_product_category`.`id`')
            ->where(['eshop_product.id' => $productIds, 'eshop_product_category.shipping' => 1])
            ->all();
        return empty($rows) ? false : true;
    }

    /**
     * React of updated payment or shipping status and set the [[Order::status]]
     * @param $orderId string integer
     */
    public static function statusUpdate($orderId)
    {
        $order = static::find()->with(['payment', 'shipping'])->where(['id' => $orderId])->one();
        if ($order->shipping !== null) {
            // We have an order with shipping
            if ($order->payment->status === PaymentStatus::COMPLETE
                && $order->shipping->status === ShippingStatus::COMPLETE) {
                $status = static::STATE_COMPLETE;
            } else {
                $status = static::STATE_PENDING;
            }
        } else {
            if ($order->payment->status === PaymentStatus::COMPLETE) {
                $status = static::STATE_COMPLETE;
            } else {
                $status = static::STATE_PENDING;
            }
        }
        $order->updateAttributes(['status' => $status]);
    }

    /*
     * Handle Stripe Webhooks Events
     */
    public static function handleStripeWebhooks($event)
    {
        $data = $event->sender->data;
        $webhook = $data->type;

        if ($webhook === 'payment_intent.succeeded') {
            $intent = $data->data->object;
        }
        Yii::info('Stripe webhook ' . $webhook . 'mit Id ' . $data->data->object->id . ' wurde empfangen.', __Method__);
    }

    /*
     * Handle product Events
     * Events triggered by Article controller
     */
    public static function handleArticleShowEvent($event)
    {
        $a = 4;
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
    public function getInvoiceAddress()
    {
        return $this->hasOne(Address::class, ['id' => 'invoice_address_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id'])->indexBy('product_id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::class, ['order_id' => 'id']);
    }
}
