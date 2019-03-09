<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_payment_status".
 *
 * @property int $id
 * @property int $payment_id
 * @property string $status
 * @property string $created_at
 * @property string $info
 *
 * @property Payment $payment
 */
class PaymentState extends \yii\db\ActiveRecord
{
    const PENDING = 'pending';
    const COMPLETE = 'complete';
    const FAIL = 'fail';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_payment_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_id', 'status'], 'required'],
            [['payment_id'], 'integer'],
            [['info'], 'string'],
            [['status'], 'string', 'max' => 64],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payment::class, 'targetAttribute' => ['payment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'payment_id' => Yii::t('eshop', 'Payment ID'),
            'status' => Yii::t('eshop', 'Status'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'info' => Yii::t('eshop', 'Info'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $payment = Payment::findOne($this->payment_id);
            $payment->updateAttributes(['status' => $this->status]);
            Order::statusUpdate($payment->order_id);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Assignments from payment methods statuss to PaymentStatus constants
     * @return mixed
     */
    public static function statusAssignment($key = null)
    {
        $assignment = [
            'paypal_rest' => [
                'success' => self::COMPLETE,
                'fail' => self::FAIL,
                'pending' => self::PENDING
            ],
            'stripe_card' => [
                'success' => self::COMPLETE,
                'fail' => self::FAIL,
                'pending' => self::PENDING
            ],
            'stripe_sepa' => [
                'success' => self::COMPLETE,
                'fail' => self::FAIL,
                'pending' => self::PENDING
            ],
        ];

        return $assignment === null ? $assignment : $assignment[$key];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::class, ['id' => 'payment_id']);
    }
}
