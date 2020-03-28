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
 * @property Customer $payment
 * @property Address $invoiceAddress
 * @property OrderProduct[] $eshopOrderProducts
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 'new';
    const STATUS_PROCESS = 'process';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETE = 'complete';

    const EVENT_STATUS_UPDATE = 'status_update';

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
            [['status'], 'required'],
            [['customer_id', 'invoice_address_id', 'payment_id'], 'integer'],
            [['total'], 'number'],
            [['notes'], 'string'],
            [['status'], 'string', 'max' => 32],
            [['ip'], 'string', 'max' => 255],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payment::class, 'targetAttribute' => ['payment_id' => 'id']],
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
            'payment_id' => Yii::t('eshop', 'Payment ID'),
            'status' => Yii::t('eshop', 'Status'),
            'total' => Yii::t('eshop', 'Total'),
            'invoice_address_id' => Yii::t('eshop', 'Invoice Address ID'),
            'ip' => Yii::t('eshop', 'Ip'),
            'notes' => Yii::t('eshop', 'Comment'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
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

    /**
     * This function is called after a Payment is insert. Now we can place the order.
     *
     * @param $payment Payment
     * @return object Order
     */
    public static function createOrder($payment, $cart)
    {
        // Create a new order
        $order = new Order();
        if ($cart->needShipping()) {
            // $shipping = new Shipping();
            // do the Shipping stuff and safe the shipping Model
        } else {
            if ($payment->status === Payment::STATUS_COMPLETE) {
                $order->status = static::STATUS_COMPLETE;
            } else {
                $order->status = static::STATUS_PROCESS;
            }
        }

        $order->customer_id = $cart->customer_id;
        $order->payment_id = $payment->id;
        $order->total = $cart->total;
        $order->ip = Yii::$app->getRequest()->getRemoteIP();
        $order->save();
        foreach ($cart->items as $item) {
            $orderItem = new OrderProduct();
            $orderItem->product_id = $item->product_id;
            $orderItem->title = $item->title;
            $orderItem->sku = $item->sku;
            $orderItem->qty = $item->qty;
            $orderItem->sell_price = $item->sell_price;
            $orderItem->link('order', $order);
        }
        return $order;
    }

    /**
     * Set the order status
     */
    public static function updateOrderStatus($id)
    {
        $order = static::findOne($id);
        $old_status = $order->status;
        if ($order->shipping !== null) {
            // We have an order with shipping
            if ($order->payment->status === Payment::STATUS_COMPLETE
                && $order->shipping->status === Shipping::STATUS_COMPLETE) {
                $status = static::STATUS_COMPLETE;
            } else {
                $status = static::STATUS_PROCESS;
            }
        } else {
            if ($order->payment->status === Payment::STATUS_COMPLETE) {
                $status = static::STATUS_COMPLETE;
            } else {
                $status = static::STATUS_PROCESS;
            }
        }
        if ($status !== $old_status) {
            $order->updateAttributes(['status' => $status]);
            static::trigger(static::EVENT_STATUS_UPDATE);
        }
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
    public function getPayment()
    {
        return $this->hasOne(Payment::class, ['id' => 'payment_id']);
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
    public function getProducts()
    {
        return $this->hasMany(OrderProduct::class, ['order_id' => 'id'])->indexBy('product_id');
    }

}
