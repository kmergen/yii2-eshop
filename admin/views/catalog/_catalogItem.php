<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $widget yii\widgets\listView */
/* @var $model kmergen\eshop\models\Article */
/* @var $key mixed */
/* @var $index integer */


if (!empty($model['mediaFiles']) && !empty($model['mediaFiles'][0]['url'])) {
    $image = Html::img(Yii::$app->image->thumb($model['mediaFiles'][0]['url'], 'xlarge'), ['class' => 'card-img-top img-fluid']);
} else {
    $image = Html::img(Yii::$app->image->placeholder('default', 'xlarge'), ['class' => 'card-img-top img-fluid']);
}

?>

<div class="card h-100">
    <?php  echo $image ?>
    <div class="card-body">
        <h5 class="card-title"><?= Html::a($model['title'], ['product/view', 'id' => $model['id']]) ?></h5>
        <p class="card-text">
        <p><?= StringHelper::truncate(Html::encode($model['description']), 50, null) ?></p>
        <p><strong><?= Html::encode(Yii::$app->formatter->asCurrency($model['sell_price'])) ?></strong></p>
        </p>

    </div>
    <div class="card-footer">
        <?= Html::button(Yii::t('eshop', 'Add to Cart'), ['class' => 'btn btn-success']) ?>
    </div>
</div>
