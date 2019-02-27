<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\stripe\models\Card */
/* @var $form yii\bootstrap4\ActiveForm */
?>
<?= $form->field($model, 'cardHolderName')->textInput() ?>
<div id="stripeCardFormGroup" class="form-group">
    <div class="stripe-card-wrapper">
        <div id="stripeCardElement">
            <!-- A Stripe Element will be inserted here. -->
        </div>
    </div>
    <div id="stripeCardErrors" class="invalid-feedback" role="alert"></div>
    <button type="button" id="stripeCardButton" data-secret="<?= $intent->client_secret ?>">
        Submit Payment
    </button>
</div>
