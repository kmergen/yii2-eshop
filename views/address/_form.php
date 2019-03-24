<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kmergen\eshop\CheckoutAsset;
use tigrov\intldata\Country;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\Address */
/* @var $form yii\widgets\ActiveForm */
CheckoutAsset::register($this);

$js = <<<JS
var floatlabels = new FloatLabels( 'form', {
style: 2
});
JS;
$this->registerJs($js,  $this::POS_END);
?>

<div class="address-form">

    <?php $form = ActiveForm::begin([
            'enableClientValidation' => false,
    ]); ?>

    <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'company')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'street')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'province')->textarea(['rows' => 5]) ?>

    <?= $form->field($model, 'country')->dropDownList(Country::names()) ?>


    <?= $form->field($model, 'phone1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone2')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('eshop', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
