<?php

namespace kmergen\eshop\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "eshop_payment".
 *
 * @property int $id
 * @property int $order_id
 * @property string $transaction_id
 * @property string $status
 * @property string $payment_method
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Order $order
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_payment';
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
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
            [['transaction_id', 'status', 'payment_method'], 'string', 'max' => 64],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
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
            'transaction_id' => Yii::t('eshop', 'Transaction ID'),
            'status' => Yii::t('eshop', 'Status'),
            'payment_method' => Yii::t('eshop', 'Payment Method'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}
