<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\Customer */

$this->title = Yii::t('eshop', 'Create Customer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Customers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
