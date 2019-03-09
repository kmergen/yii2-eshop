<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $widget yii\widgets\listView */
/* @var $model kmergen\eshop\models\Article */
/* @var $key mixed */
/* @var $index integer */


if (!empty($model['mediaFiles']) && !empty($model['mediaFiles'][0]['url'])) {
    $image = Html::img(Yii::$app->image->thumb($model['mediaFiles'][0]['url'], 'small'));
} else {
    $image = Html::img(Yii::$app->image->placeholder('default', 'small'));
}

?>
<div class="row no-gutters">
    <div class="col-1">
        <?= 'ID: ' . $model['id'] ?>
    </div>
    <div class="col-1">
        <?= 'SKU: ' . $model['sku'] ?>
    </div>
    <div class="col-2">
        <?= $image ?>
    </div>

    <div class="col-6">
        <h5><?= Html::a(Html::encode($model['title']), ['product/view', 'id' => $model['id']]) ?></h5>
        <p><?= StringHelper::truncate(Html::encode($model['description']), 50, ' ...') ?></p>
    </div>
    <div class="col-1">
        <?= Html::encode(Yii::$app->formatter->asCurrency($model['sell_price'])) ?>
    </div>
    <div class="col-1">
        <?= $model['created_at'] ?>
    </div>
</div>
