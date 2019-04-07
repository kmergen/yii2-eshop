<?php

namespace kmergen\eshop\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * This is the model class for table "eshop_payment".
 *
 * @property int $id
 * @property int $cart_id
 * @property string $transaction_id
 * @property string $payment_method
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property resource $data
 *
 * @property Cart $cart
 * @property PaymentStatus[] $PaymentStatuses
 */
class Payment extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESS = 'process';
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_UNKNOWN = 'unknown';

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
            [['cart_id'], 'integer'],
            [['cart_id', 'transaction_id'], 'required'],
            [['data'], 'string'],
            [['transaction_id', 'payment_method', 'status'], 'string', 'max' => 64],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cart::class, 'targetAttribute' => ['cart_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'cart_id' => Yii::t('eshop', 'Cart ID'),
            'transaction_id' => Yii::t('eshop', 'Transaction ID'),
            'payment_method' => Yii::t('eshop', 'Payment Method'),
            'status' => Yii::t('eshop', 'Status'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
            'data' => Yii::t('eshop', 'Data'),
        ];
    }

    public function getPaypalData() {
        return \unserialize($this->data);
    }

    public function setPaypalData($data) {
        $this->data = serialize($data);
    }

    public function getStripeData() {
        return Json::decode($this->data);
    }

    public function setStripeData($data) {
        $this->data = Json::encode($data);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::class, ['id' => 'cart_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentStatuses()
    {
        return $this->hasMany(EshopPaymentStatus::className(), ['payment_id' => 'id']);
    }

}
