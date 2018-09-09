<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\Order */

$this->title = Yii::t('eshop', 'Create Order');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
