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
 * @property OrderProduct[] $eshopOrderProducts
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
    public function getProducts()
    {
        return $this->hasMany(OrderProduct::class, ['order_id' => 'id'])->indexBy('product_id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::class, ['order_id' => 'id']);
    }
}
