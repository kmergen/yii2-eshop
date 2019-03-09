<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\ArticleCategory */

$this->title = Yii::t('eshop', 'Create Article Category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('eshop', 'Article Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
