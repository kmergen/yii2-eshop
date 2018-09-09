<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use kmergen\widgets\DropdownButtonSorter;

/* @var $this yii\web\View */
/* @var $searchModel kmergen\eshop\models\ArticleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerMetaTag(['name' => 'description', 'content' => Yii::t('eshop', 'catalog.meta.description')]);
$this->registerMetaTag(['name' => 'keywords', 'content' => Yii::t('eshop', 'catalog.meta.keywords')]);

$this->title = Yii::t('eshop', 'catalog.meta.title');
$this->params['breadcrumbs'][] = Yii::t('eshop', 'Catalog');

?>
<div class="catalog-index">
    <h1><?= Yii::t('eshop', 'catalog.title') ?></h1>

    <?php
    echo $this->render('_search', [
        'model' => $searchModel
    ]);

    ?>


        <?php $b = ListView::begin([
            'dataProvider' => $dataProvider,
            'options' => ['class' => 'row'],
            'itemView' => '_catalogItem',
            'layout' => "{items}\n{pager}",
            'itemOptions' => ['class' => 'col-sm-12 col-md-4 col-lg-3 mb-4'],
            'sorter' => [
                'class' => DropdownButtonSorter::class,
            ]
        ]) ?>
        <?php ListView::end(); ?>

</div>
