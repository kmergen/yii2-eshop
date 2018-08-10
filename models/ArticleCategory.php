<?php

namespace app\modules\eshop\models;

/**
 * This is the model class for table "eshop_article_category".
 *
 * @property string $id
 * @property string $name
 * @property string $parent
 *
 * @property EshopArticle[] $eshopArticles
 */
class ArticleCategory extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'eshop_article_category';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'name'], 'required'],
			[['id', 'parent'], 'integer'],
			[['name'], 'string', 'max' => 100]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'name' => 'Name',
			'parent' => 'Parent',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getEshopArticles()
	{
		return $this->hasMany(EshopArticle::className(), ['category_id' => 'id']);
	}
}
