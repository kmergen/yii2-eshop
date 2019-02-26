<?php

namespace kmergen\eshop\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "eshop_customer".
 *
 * @property int $id
 * @property string $email
 * @property int $user_id The user id from the yii application
 * @property string $birthday
 * @property string $gender
 * @property string $created_at
 * @property string $updated_at
 *
 * @property EshopOrder[] $eshopOrders
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
            [['email'], 'required'],
            [['user_id'], 'integer'],
            [['birthday'], 'safe'],
            [['email'], 'string', 'max' => 255],
            [['gender'], 'string', 'max' => 1],
            [['email'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'email' => Yii::t('eshop', 'Email'),
            'user_id' => Yii::t('eshop', 'User ID'),
            'birthday' => Yii::t('eshop', 'Birthday'),
            'gender' => Yii::t('eshop', 'Gender'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(Address::class, ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEshopOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}
