<?php

use yii\helpers\Html;
use kmergen\widgets\DropdownButtonSorter;
use yii\grid\GridView;
use kmergen\eshop\grid\ActionColumn;
use kmergen\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel kmergen\eshop\models\ArticleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerMetaTag(['name' => 'description', 'content' => Yii::t('eshop', 'product.meta.description')]);
$this->registerMetaTag(['name' => 'keywords', 'content' => Yii::t('eshop', 'product.meta.keywords')]);

$this->title = Yii::t('eshop', 'product.meta.title');
$this->params['breadcrumbs'][] = Yii::t('eshop', 'Article');

?>
<div class="product-index">
    <h1><?= Yii::t('eshop', 'product.title') ?></h1>

    <p>
        <?= Html::a(Yii::t('eshop', 'Create Article'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'sku',
            'category_id',
            'title',
            'description',
            'sell_price',
            'created_at',
            //'province',
            //'country_code',
            //'phone1',
            //'phone2',
            //'created_at',
            //'updated_at',

            ['class' => ActionColumn::class],
        ],
    ]); ?>
</div>
