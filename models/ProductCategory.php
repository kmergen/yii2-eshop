<?php

namespace kmergen\eshop\models;

use Yii;

/**
 * This is the model class for table "eshop_product_category".
 *
 * @property int $id
 * @property string $name
 * @property int $parent
 * @property int $shipping
 *
 * @property Article[] $products
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_product_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['id', 'parent', 'shipping'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['id'], 'unique'],
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
            'parent' => Yii::t('eshop', 'Parent'),
            'shipping' => Yii::t('eshop', 'Shipping'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['category_id' => 'id']);
    }
}
