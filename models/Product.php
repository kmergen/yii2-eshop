<?php

namespace kmergen\eshop\models;

use Yii;
use kmergen\media\behaviors\MediaAlbumBehavior;
use kmergen\media\models\Media;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\filters\AccessControl;

/**
 * This is the model class for table "eshop_product".
 *
 * @property int $id The product id
 * @property string $sku SKU or model number.
 * @property int $category_id FK The category id from table eshop_product_category
 * @property string $title The title of the product
 * @property string $description The description of the product
 * @property string $sell_price
 * @property int $default_qty
 * @property int $max_qty
 * @property int $active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ArticleCategory $category
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eshop_product';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'modelimages' => [
                'class' => MediaAlbumBehavior::class,
                'attribute' => 'media_album_id',
            ],
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
            [['title', 'sku', 'category_id', 'description', 'sell_price'], 'required'],
            [['category_id', 'default_qty', 'max_qty', 'active'], 'integer'],
            [['description'], 'string'],
            [['sell_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['sku'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 150],
            [['sku'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArticleCategory::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('eshop', 'ID'),
            'sku' => Yii::t('eshop', 'Sku'),
            'category_id' => Yii::t('eshop', 'Category ID'),
            'title' => Yii::t('eshop', 'Title'),
            'description' => Yii::t('eshop', 'Description'),
            'sell_price' => Yii::t('eshop', 'Sell Price'),
            'default_qty' => Yii::t('eshop', 'Default Qty'),
            'active' => Yii::t('eshop', 'Active'),
            'created_at' => Yii::t('eshop', 'Created At'),
            'updated_at' => Yii::t('eshop', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ArticleCategory::class, ['id' => 'category_id']);
    }

    /**
     * Because we retrieving records as arrays from model in [[ArticleSearch]] class we use this ActiveQuery to get the images,
     * because afterFind() function in [[kmergen\media\behaviors\MediaAlbumBehavior]] not called.
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Media::class, ['album_id' => 'media_album_id']);
    }
}
