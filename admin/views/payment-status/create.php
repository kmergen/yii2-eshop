<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\PaymentStatus */

$this->title = Yii::t('eshop', 'Create Payment Status');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Payment Statuses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-status-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
