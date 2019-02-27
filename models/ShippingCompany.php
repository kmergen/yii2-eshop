<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_shipping_company".
 *
 * @property int $id
 * @property string $name
 *
 * @property Shipping[] $Shippings
 */
class ShippingCompany extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_shipping_company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'name' => Yii::t('eshop', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShippings()
    {
        return $this->hasMany(Shipping::class, ['shipping_company_id' => 'id']);
    }
}
