<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\stripe\models\Card */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $intent Stripe\PaymentIntent */
?>
<?= $form->field($model, 'cardHolderName')->textInput() ?>
<div id="stripeCardFormGroup" class="form-group">
    <div class="stripe-card-wrapper">
        <div id="stripeCardElement">
            <!-- A Stripe Element will be inserted here. -->
        </div>
    </div>
    <div id="stripeCardErrors" class="invalid-feedback" role="alert"></div>
    <input type="hidden" name="stripeClientSecret" id="stripeClientSecret" value="<?= $intent->client_secret ?>">
</div>
