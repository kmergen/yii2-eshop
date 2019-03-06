<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\ShippingCompany */

$this->title = Yii::t('eshop', 'Create Shipping Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Shipping Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="shipping-company-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
