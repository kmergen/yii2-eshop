<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\stripe\models\Sepa */
/* @var $form yii\bootstrap4\ActiveForm */

$css = <<<CSS
.form-group.is-invalid .stripe-iban-wrapper {
border-color: #dc3545;
}
.form-group.is-valid .stripe-iban-wrapper {
border-color: #28a745;
}
.stripe-iban-wrapper {
border: 1px solid #ced4da;
padding: .5rem;
}
.iban-wrapper label {
font-size: .875rem;
font-style: italic;
font-weight: 500;
}
CSS;

$this->registerCss($css);
?>

<?= $form->field($model, 'bankaccountOwner')->textInput() ?>
<?= $form->field($model, 'email',
    ['template' => "{input}\n"])->hiddenInput()->label(false) ?>


<div id="stripeIbanFormGroup" class="form-group">
    <div class="stripe-iban-wrapper">
        <label for="stripeIbanElement">IBAN</label>
        <div id="stripeIbanElement">
            <!-- A Stripe Element will be inserted here. -->
        </div>
        <div id="stripeBankName"></div>
    </div>
    <div id="stripeIbanErrors" class="invalid-feedback"></div>
</div>

<!-- Display mandate acceptance text. -->
<div id="mandate-acceptance">
    <?= Yii::t('eshop/checkout', 'stripe.iban.mandate-acceptance-text') ?>
</div>
