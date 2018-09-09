<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\Address */

$this->title = Yii::t('eshop', 'Create Address');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Addresses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="address-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
