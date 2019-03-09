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
 * @property string $data A serialized array of extra data.
 * @property string $ip Host IP address of the person paying for the order.
 * @property string $notes Order notes
 * @property string $created_at
 * @property string $updated_at
 *
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
            [['data', 'notes'], 'string'],
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
            'data' => Yii::t('eshop', 'Data'),
            'ip' => Yii::t('eshop', 'Ip'),
            'notes' => Yii::t('eshop', 'Comment'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
    }

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
        }
    }

    /**
     * Remove Item from Cart
     * @param $id integer kmergen\eshop\Product id
     */
    public function removeItem($id)
    {
        if (($item = $this->getItem($id)) !== null) {
            $this->unlink('items', $item);
        }
    }

    /**
     * Return kmergen\eshop\models\Product Object or null if it not exist
     * @param $id integer kmergen\eshop\models\Product Id
     * @return mixed
     */
    public function getItem($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
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
