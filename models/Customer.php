<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_customer".
 *
 * @property int $id
 * @property int $address_id
 * @property string $email
 * @property int $user_id The user id from the yii application
 * @property string $birthday
 * @property string $gender
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Address[] $eshopAddresses
 * @property Address $address
 * @property Order[] $Orders
 */
class Customer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['address_id', 'email'], 'required'],
            [['address_id', 'user_id'], 'integer'],
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['email'], 'string', 'max' => 255],
            [['gender'], 'string', 'max' => 1],
            [['email'], 'unique'],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::class, 'targetAttribute' => ['address_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'address_id' => Yii::t('app', 'Address ID'),
            'email' => Yii::t('app', 'Email'),
            'user_id' => Yii::t('app', 'User ID'),
            'birthday' => Yii::t('app', 'Birthday'),
            'gender' => Yii::t('app', 'Gender'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(Address::class, ['id' => 'address_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
