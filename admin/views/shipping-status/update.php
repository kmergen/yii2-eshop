<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\ShippingStatus */

$this->title = Yii::t('eshop', 'Update Shipping Status: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Shipping Statuses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('eshop', 'Update');
?>
<div class="shipping-status-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
