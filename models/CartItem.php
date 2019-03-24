<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_cart_item".
 *
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property string $title
 * @property string $sku
 * @property int $qty
 * @property string $sell_price
 *
 * @property Product $product
 * @property Cart $cart
 */
class CartItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_cart_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cart_id', 'product_id', 'qty', 'sell_price'], 'required'],
            [['cart_id', 'product_id', 'qty'], 'integer'],
            [['sell_price'], 'number'],
            [['title', 'sku'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => Yii::t('eshop', 'Product ID'),
            'title' => Yii::t('eshop', 'Title'),
            'sku' => Yii::t('eshop', 'Sku'),
            'qty' => Yii::t('eshop', 'Qty'),
            'sell_price' => Yii::t('eshop', 'Sell Price'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::class, ['id' => 'cart_id']);
    }
}
