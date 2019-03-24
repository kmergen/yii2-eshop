<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_address".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $fullname
 * @property string $company
 * @property string $street
 * @property string $postcode
 * @property string $city
 * @property string $province
 * @property string $province_code
 * @property string $country
 * @property string $phone1
 * @property string $phone2
 * @property string $latitude
 * @property string $longitude
 *
 * @property EshopCustomer $customer
 * @property EshopCart[] $eshopCarts
 * @property EshopCart[] $eshopCarts0
 * @property EshopOrder[] $eshopOrders
 * @property EshopOrder[] $eshopOrders0
 * @property EshopShipping[] $eshopShippings
 */
class Address extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_address';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'postcode', 'city', 'country', 'phone1', 'phone2'], 'required'],
            [['customer_id'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['fullname', 'company', 'street'], 'string', 'max' => 255],
            [['postcode'], 'string', 'max' => 6],
            [['city', 'province'], 'string', 'max' => 150],
            [['province_code'], 'string', 'max' => 5],
            [['country'], 'string', 'max' => 2],
            [['phone1', 'phone2'], 'string', 'max' => 50],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
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
            'fullname' => Yii::t('eshop', 'Fullname'),
            'company' => Yii::t('eshop', 'Company'),
            'street' => Yii::t('eshop', 'Street'),
            'postcode' => Yii::t('eshop', 'Postcode'),
            'city' => Yii::t('eshop', 'City'),
            'province' => Yii::t('eshop', 'Province'),
            'province_code' => Yii::t('eshop', 'Province Code'),
            'country' => Yii::t('eshop', 'Country'),
            'phone1' => Yii::t('eshop', 'Phone1'),
            'phone2' => Yii::t('eshop', 'Phone2'),
            'latitude' => Yii::t('eshop', 'Latitude'),
            'longitude' => Yii::t('eshop', 'Longitude'),
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
    public function getEshopCarts()
    {
        return $this->hasMany(Cart::class, ['invoice_address_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEshopCarts0()
    {
        return $this->hasMany(Cart::class, ['shipping_address_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEshopOrders()
    {
        return $this->hasMany(EshopOrder::className(), ['invoice_address_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEshopOrders0()
    {
        return $this->hasMany(EshopOrder::className(), ['shipping_address_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEshopShippings()
    {
        return $this->hasMany(EshopShipping::className(), ['shipping_address_id' => 'id']);
    }
}
