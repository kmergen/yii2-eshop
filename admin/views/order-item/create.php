<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\OrderItem */

$this->title = Yii::t('eshop', 'Create Order Item');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Order Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
