<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kmergen\eshop\grid\ActionColumn;
use kmergen\widgets\LinkPager;


/* @var $this yii\web\View */
/* @var $searchModel kmergen\eshop\models\ArticleCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('eshop', 'Article Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('eshop', 'Create Article Category'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'parent',
            'shipping',

            ['class' => ActionColumn::class],
        ],
    ]); ?>
</div>
