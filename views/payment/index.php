<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kmergen\eshop\grid\ActionColumn;
use kmergen\widgets\LinkPager;


/* @var $this yii\web\View */
/* @var $searchModel kmergen\eshop\models\PaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('eshop', 'Payments');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('eshop', 'Create Payment'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'transaction_id',
            'state',
            'payment_method',
            //'created_at',
            //'updated_at',

            ['class' => ActionColumn::class],
        ],
    ]); ?>
</div>
