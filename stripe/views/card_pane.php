<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\stripe\models\Card */
/* @var $form yii\widgets\ActiveForm */
?>
<div id="card-form-group" class="form-group">
    <div class="card-wrapper">
        <div id="stripeCardElement">
            <!-- A Stripe Element will be inserted here. -->
        </div>
    </div>
        <div id="card-errors" class="pane-errors invalid-feedback" role="alert"></div>
</div>

<?php
$js = <<<JS
var stripe = Stripe('pk_test_X9alOw25WC8wUGquMDlQctgS');
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
var style = {
  base: {
    // Add your base input styles here. For example:
    fontSize: '16px',
    color: "#32325d",
  }
};

// Create an instance of the card Element.
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `strip card element` <div>.
card.mount('#stripeCardElement');

var invalidFeedback = document.getElementById('card-errors');
var cardFormGroup = document.getElementById('card-form-group')
 
card.addEventListener('change', function(event) {
  if (event.error) {
    invalidFeedback.textContent = event.error.message;
    ibanFormGroup.classList.add('is-invalid');
  } else {
    ibanFormGroup.classList.remove('is-invalid');
    invalidFeedback.textContent = '';
  }
});

// Create a token or display an error when the form is submitted.
var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  event.preventDefault();

  stripe.createToken(card).then(function(result) {
    if (result.error) {
      // Inform the customer that there was an error.
      invalidFeedback.textContent = result.error.message;
      ibanFormGroup.classList.add('is-invalid');
    } else {
      ibanFormGroup.classList.remove('is-invalid');
      // Send the token to your server.
      stripeTokenHandler(result.token);
    }
  });
});

function stripeTokenHandler(token) {
  // Insert the token ID into the form so it gets submitted to the server
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  form.appendChild(hiddenInput);
}

JS;

$this->registerJs($js, $this::POS_END);
?>
