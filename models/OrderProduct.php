<?php

namespace kmergen\eshop\models;

use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "eshop_order_item".
 *
 * @property int $order_id The sc_order.order_id.
 * @property int $product_id The product id from table product
 * @property string $title The product title, from node.title.
 * @property string $sku The product model/SKU, from sc_products.model.
 * @property int $qty
 * @property string $sell_price
 * @property string $data A serialized array of extra data.
 *
 * @property Order $order
 */
class OrderProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_order_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'qty', 'sell_price'], 'required'],
            [['order_id', 'product_id', 'qty'], 'integer'],
            [['sell_price'], 'number'],
            [['title', 'sku'], 'string', 'max' => 255],
            [['order_id', 'product_id'], 'unique', 'targetAttribute' => ['order_id', 'product_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'product_id' => Yii::t('app', 'ArticleOld ID'),
            'title' => Yii::t('app', 'Title'),
            'sku' => Yii::t('app', 'Sku'),
            'qty' => Yii::t('app', 'Qty'),
            'sell_price' => Yii::t('app', 'Sell Price'),
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
