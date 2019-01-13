<?php

namespace kmergen\eshop\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "eshop_order".
 *
 * @property int $id Primary key: the order ID.
 * @property int $customer_id
 * @property string $status The order status.
 * @property string $total
 * @property int $invoice_address_id
 * @property int $shipping_address_id
 * @property string $data A serialized array of extra data.
 * @property string $ip Host IP address of the person paying for the order.
 * @property string $comment Order comment
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer $customer
 * @property Address $invoiceAddress
 * @property Address $shippingAddress
 * @property OrderItem[] $eshopOrderItems
 * @property Payment[] $eshopPayments
 */
class Order extends \yii\db\ActiveRecord
{
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
            [['customer_id', 'total'], 'required'],
            [['customer_id', 'invoice_address_id', 'shipping_address_id'], 'integer'],
            [['total'], 'number'],
            [['data', 'comment'], 'string'],
            [['status'], 'string', 'max' => 32],
            [['ip'], 'string', 'max' => 255],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['invoice_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::class, 'targetAttribute' => ['invoice_address_id' => 'id']],
            [['shipping_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::class, 'targetAttribute' => ['shipping_address_id' => 'id']],
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
            'shipping_address_id' => Yii::t('eshop', 'Shipping Address ID'),
            'data' => Yii::t('eshop', 'Data'),
            'ip' => Yii::t('eshop', 'Ip'),
            'comment' => Yii::t('eshop', 'Comment'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
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
    public function getShippingAddress()
    {
        return $this->hasOne(Address::class, ['id' => 'shipping_address_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['order_id' => 'id']);
    }
}
