<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\stripe\models\Card */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $intent Stripe\PaymentIntent */
?>
<?= $form->field($model, 'cardHolderName')->textInput() ?>
<?= $form->field($model, 'clientSecret',
    ['template' => "{input}\n"])->hiddenInput()->label(false) ?>
<?= $form->field($model, 'intentId',
    ['template' => "{input}\n"])->hiddenInput()->label(false) ?>
<div id="stripeCardFormGroup" class="form-group">
    <div class="stripe-card-wrapper">
        <div id="stripeCardElement">
            <!-- A Stripe Element will be inserted here. -->
        </div>
    </div>
    <div id="stripeCardErrors" class="invalid-feedback" role="alert"></div>
</div>
