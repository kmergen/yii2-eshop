<?php

namespace app\modules\eshop\models;

/**
 * This is the model class for table "eshop_article".
 *
 * @property string $id
 * @property string $category_id
 * @property string $sku
 * @property string $title
 * @property string $description
 * @property string $sell_price
 * @property integer $default_qty
 * @property integer $selectable
 * @property integer $ordering
 *
 * @property EshopArticleCategory $category
 */
class Article extends \yii\db\ActiveRecord
{
    const ARTICLE_TYPE_STANDARD = 1; // The standard Article with shipping
    const ARTICLE_TYPE_ESD = 2; // A ELECTRONIC SOFTWARE DISTRIBUTIN Article that you can download in the shop after payment without shipping
    
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'eshop_article';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['category_id', 'description', 'selectable'], 'required'],
			[['category_id', 'default_qty', 'selectable', 'ordering'], 'integer'],
			[['description'], 'string'],
			[['sell_price'], 'number'],
			[['sku'], 'string', 'max' => 255],
			[['title'], 'string', 'max' => 150]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'category_id' => 'Category ID',
			'sku' => 'Sku',
			'title' => 'Title',
			'description' => 'Description',
			'sell_price' => 'Sell Price',
			'default_qty' => 'Default Qty',
			'selectable' => 'Selectable',
			'ordering' => 'Ordering',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getCategory()
	{
		return $this->hasOne(EshopArticleCategory::className(), ['id' => 'category_id']);
	}
}
