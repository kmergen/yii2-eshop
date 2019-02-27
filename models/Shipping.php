<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_shipping".
 *
 * @property int $id
 * @property int $order_id
 * @property int $shipping_address_id
 * @property int $shipping_company_id
 * @property string $status
 * @property resource $data
 *
 * @property Address $shippingAddress
 * @property Order $order
 * @property ShippingCompany $shippingCompany
 * @property ShippingStatus[] $eshopShippingStatuses
 */
class Shipping extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_shipping';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'shipping_address_id', 'shipping_company_id'], 'required'],
            [['order_id', 'shipping_address_id', 'shipping_company_id'], 'integer'],
            [['data'], 'string'],
            [['status'], 'string', 'max' => 255],
            [['shipping_address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::class, 'targetAttribute' => ['shipping_address_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['shipping_company_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShippingCompany::class, 'targetAttribute' => ['shipping_company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'order_id' => Yii::t('eshop', 'Order ID'),
            'shipping_address_id' => Yii::t('eshop', 'Shipping Address ID'),
            'shipping_company_id' => Yii::t('eshop', 'Shipping Company ID'),
            'status' => Yii::t('eshop', 'Status'),
            'data' => Yii::t('eshop', 'Data'),
        ];
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
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShippingCompany()
    {
        return $this->hasOne(ShippingCompany::class, ['id' => 'shipping_company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShippingStatuses()
    {
        return $this->hasMany(ShippingStatus::class, ['shipping_id' => 'id']);
    }
}
