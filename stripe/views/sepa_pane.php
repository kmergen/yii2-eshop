<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\stripe\models\Sepa */
/* @var $form yii\widgets\ActiveForm */

$css = <<<CSS
.iban-wrapper {
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

<?= $form->field($model, 'bankaccountOwner') ?>
<?= $form->field($model, 'email', ['template' => "{input}\n"])->hiddenInput() ?>


<div id="iban-form-group" class="form-group is-invalid">
    <div class="iban-wrapper">
        <label for="iban-element">IBAN</label>
        <div id="iban-element">
            <!-- A Stripe Element will be inserted here. -->
        </div>
        <div id="bank-name"></div>
    </div>
    <div id="iban-errors" class="invalid-feedback"></div>
</div>

<!-- Display mandate acceptance text. -->
<div id="mandate-acceptance">
    By providing your IBAN and confirming this payment, you are
    authorizing Rocketship Inc. and Stripe, our payment service
    provider, to send instructions to your bank to debit your account and
    your bank to debit your account in accordance with those instructions.
    You are entitled to a refund from your bank under the terms and
    conditions of your agreement with your bank. A refund must be claimed
    within 8 weeks starting from the date on which your account was debited.
</div>

<?php
$js = <<<JS
//var stripe = Stripe('pk_test_X9alOw25WC8wUGquMDlQctgS');
//var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
  base: {
    color: '#32325d',
    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
    fontSmoothing: 'antialiased',
    fontSize: '16px',
    '::placeholder': {
      color: '#aab7c4'
    },
    ':-webkit-autofill': {
      color: '#32325d',
    },
  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a',
    ':-webkit-autofill': {
      color: '#fa755a',
    },
  }
};

// Create an instance of the iban Element.
var iban = elements.create('iban', {
  style: style,
  supportedCountries: ['SEPA'],
});

// Add an instance of the iban Element into the `iban-element` <div>.
iban.mount('#iban-element');

var ibanFormGroup = document.getElementById('iban-form-group')
var invalidFeedback = document.getElementById('iban-errors');
var bankName = document.getElementById('bank-name');

iban.on('change', function(event) {
  // Handle real-time validation errors from the iban Element.
  if (event.error) {
    invalidFeedback.textContent = event.error.message;
    ibanFormGroup.classList.add('is-invalid');
  } else {
    ibanFormGroup.classList.remove('is-invalid');
  }

  // Display bank name corresponding to IBAN, if available.
  if (event.bankName) {
    bankName.textContent = event.bankName;
    bankName.classList.add('d-block');
  } else {
    bankName.classList.remove('d-block');
    bankName.classList.add('d-none');
  }
});

// Handle form submission.
var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  // Only if this is the active payment method
  if (activePaymentMethod === '')
  event.preventDefault();
  // showLoading();

  var sourceData = {
    type: 'sepa_debit',
    currency: 'eur',
    owner: {
      name: document.querySelector('input[name="Sepa[bankaccountOwner]"]').value,
      email: document.querySelector('input[name="Sepa[email]"]').value,
    },
    mandate: {
      // Automatically send a mandate notification email to your customer
      // once the source is charged.
      notification_method: 'email',
    }
  };

  // Call `stripe.createSource` with the iban Element and additional options.
  stripe.createSource(iban, sourceData).then(function(result) {
    if (result.error) {
      // Inform the customer that there was an error.
      invalidFeedback.textContent = result.error.message;
      ibanFormGroup.classList.add('is-invalid');
      // stopLoading();
    } else {
      // Send the Source to your server to create a charge.
      ibanFormGroup.classList.remove('is-invalid');
      stripeSourceHandler(result.source);
    }
  });
});

function stripeSourceHandler(source) {
  // Insert the Source ID into the form so it gets submitted to the server.
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeSource');
  hiddenInput.setAttribute('value', source.id);
  form.appendChild(hiddenInput);

  // If there are no other errors ActiveForm will submit the form to the server.
}

JS;

$this->registerJs($js, $this::POS_END);
?>
