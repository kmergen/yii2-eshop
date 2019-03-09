<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\ShippingStatus */

$this->title = Yii::t('eshop', 'Create Shipping Status');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Shipping Statuses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="shipping-state-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
