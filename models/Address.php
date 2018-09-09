<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_address".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $firstname
 * @property string $lastname
 * @property string $company
 * @property string $street
 * @property string $city
 * @property string $province
 * @property string $country_code
 * @property string $phone1
 * @property string $phone2
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer $customer
 * @property EshopOrder[] $eshopOrders

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
            [['customer_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['firstname', 'lastname', 'company', 'street', 'city', 'province', 'country_code', 'phone1', 'phone2'], 'string', 'max' => 255],
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
            'firstname' => Yii::t('eshop', 'Firstname'),
            'lastname' => Yii::t('eshop', 'Lastname'),
            'company' => Yii::t('eshop', 'Company'),
            'street' => Yii::t('eshop', 'Street'),
            'city' => Yii::t('eshop', 'City'),
            'province' => Yii::t('eshop', 'Province'),
            'country_code' => Yii::t('eshop', 'Country Code'),
            'phone1' => Yii::t('eshop', 'Phone1'),
            'phone2' => Yii::t('eshop', 'Phone2'),
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
    public function getOrderInnvoiceAddresses()
    {
        return $this->hasMany(Order::class, ['invoice_address_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersShippingAddresses()
    {
        return $this->hasMany(Order::class, ['shipping_address_id' => 'id']);
    }
}
