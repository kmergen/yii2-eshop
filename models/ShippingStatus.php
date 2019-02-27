<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_shipping_status".
 *
 * @property int $id
 * @property int $shipping_id
 * @property string $status
 * @property string $created_at
 * @property string $info
 *
 * @property Shipping $shipping
 */
class ShippingStatus extends \yii\db\ActiveRecord
{
    const PENDING = 'pending';
    const COMPLETE = 'complete';
    const FAIL = 'fail';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_shipping_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shipping_id', 'status', 'created_at'], 'required'],
            [['shipping_id'], 'integer'],
            [['created_at'], 'safe'],
            [['info'], 'string'],
            [['status'], 'string', 'max' => 64],
            [['shipping_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shipping::class, 'targetAttribute' => ['shipping_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'shipping_id' => Yii::t('eshop', 'Shipping ID'),
            'status' => Yii::t('eshop', 'Status'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'info' => Yii::t('eshop', 'Info'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave();

        if ($insert) {
            if (\array_key_exists('status', $changedAttributes)) {
                $status = $changedAttributes['status'];
                $shipping = Shipping::findOne($this->shipping_id);
                $shipping->updateAttributes(['status' => $status]);
                Order::statusUpdate($shipping->order_id);
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShipping()
    {
        return $this->hasOne(Shipping::class, ['id' => 'shipping_id']);
    }
}
