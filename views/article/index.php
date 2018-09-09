<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use kmergen\widgets\DropdownButtonSorter;

/* @var $this yii\web\View */
/* @var $searchModel kmergen\eshop\models\ArticleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerMetaTag(['name' => 'description', 'content' => Yii::t('eshop', 'article.meta.description')]);
$this->registerMetaTag(['name' => 'keywords', 'content' => Yii::t('eshop', 'article.meta.keywords')]);

$this->title = Yii::t('eshop', 'article.meta.title');
$this->params['breadcrumbs'][] = Yii::t('eshop', 'Article');

?>
<div class="article-index">
    <h1><?= Yii::t('eshop', 'article.title') ?></h1>

    <p>
        <?= Html::a(Yii::t('eshop', 'Create Article'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    echo $this->render('_search', [
        'model' => $searchModel
    ]);

    ?>

    <div class="row">
        <div class="col-12">
            <?php $b = ListView::begin([
                'dataProvider' => $dataProvider,
                'itemView' => '_articleItem',
                'layout' => "{items}\n{pager}",
                'itemOptions' => ['class' => 'article-item'],
                'sorter' => [
                    'class' => DropdownButtonSorter::class,
                ]
            ]) ?>
            <?php ListView::end(); ?>
        </div>
    </div>
</div>
