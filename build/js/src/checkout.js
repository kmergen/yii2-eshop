/* global jQuery */
/* global KMeshop */
// Checkout Javascript

KMeshop.checkout = function ($) {
    // Public goes here
    const pub = {
        init: function (options) {
            $.extend(settings, defaults, options)
            checkoutForm = document.getElementById(settings.CHECKOUT_FORM_ID);
            paymentWall = document.getElementById(settings.PAYMENT_WALL_ID);
            initEvents();
        }
    }

    //Private goes here
    const defaults = {
        active: true,
        CHECKOUT_FORM_ID: 'checkoutForm',
        PAYMENT_WALL_ID: 'paymentWall',
        CANCEL_BUTTON_ID: 'btnCancel',
        CHECKOUT_CANCELED_ID: 'checkoutform-checkoutcanceled',
        PAYMENT_METHOD_ID: 'checkoutform-paymentmethod',
        PANE_CONTENT_SELECTOR: '.card-body'
    }

    let settings = {}
    let checkoutForm = undefined;
    let paymentWall = undefined;

    // global Stripe variables
    let stripeIban = undefined
    let stripeCard = undefined

    /*eslint-disable */
    function paymentMethodsCallbacks(paymentMethod, action, data) {
        switch (paymentMethod) {
            case 'paypal_rest':
                if (action === 'add') {
                    addPaypalRest()
                } else if (action === 'remove') {
                    removePaypalRest()
                } else if (action === 'submit') {
                    submitPaypalRest()
                }
                break
            case 'stripe_card':
                if (action === 'add') {
                    addStripeCard()
                } else if (action === 'remove') {
                    removeStripeCard()
                } else if (action === 'submit') {
                    submitStripeCard()
                }
                break
            case 'stripe_sepa':
                if (action === 'add') {
                    addStripeSepa()
                    addSepaValidation(data.errorMessages);
                } else if (action === 'remove') {
                    removeStripeSepa()
                } else if (action === 'submit') {
                    submitStripeSepa()
                }
                break
            default:
                return
        }
    }

    /*eslint-enable */

    function getPaymentMethod() {
        return document.getElementById(settings.PAYMENT_METHOD_ID).value;
    }

    function setPaymentMethod(val) {
        document.getElementById(settings.PAYMENT_METHOD_ID).value = val;
    }

    /* Callback function from Paymentmethod submitFunction
    This is the final function that the active payment method use as callback
    The data parameter is an object with specified keys */
    function checkoutFinal() {
        checkoutForm.submit();
    }

    function initEvents() {
        /*eslint-disable */

        // $(checkoutForm).on('submit', function (event) {
        //     event.preventDefault();
        //     const $form = $(this)
        //     let $data = $form.data("yiiActiveForm");
        //     $.each($data.attributes, function() {
        //         this.status = 3;
        //     });
        //     $form.yiiActiveForm('validate')
        // })

        /* This function is called if client validation is successful.
           But we always return false because the [[paymentMethodsCallbacks()]]
           will call [[checkoutFinal]] to submit the form. */
        $(checkoutForm).on('beforeSubmit', function (event) {
            paymentMethodsCallbacks(getPaymentMethod(), 'submit')
            return false;
        });

        /*eslint-enable */
        $(paymentWall).on('show.bs.collapse', function (event) {
            var el = $(event.target);
            $.ajax(el.data('paneurl'))
                .done(function (data) {
                    el.children(settings.PANE_CONTENT_SELECTOR).html(data.html)
                    let paymentmethod = el.data('paymentmethod');
                    setPaymentMethod(paymentmethod)
                    paymentMethodsCallbacks(paymentmethod, 'add', data)
                    $(checkoutForm).yiiActiveForm('validateAttribute', 'checkoutform-paymentmethod');
                })
                .fail(function () {
                    //  alert( 'error' );
                })
        })

        $(paymentWall).on('hide.bs.collapse', function (event) {
            const el = $(event.target);
            const paymentmethod = getPaymentMethod()
            setPaymentMethod('');
            paymentMethodsCallbacks(paymentmethod, 'remove')
            el.children(settings.PANE_CONTENT_SELECTOR).empty();

        })

        $(document.getElementById(settings.CANCEL_BUTTON_ID)).on('click', function () {
            document.getElementById(settings.CHECKOUT_CANCELED_ID).value = '1';
            checkoutForm.submit();
        });
    }

    /*eslint-disable */

    // Payment method specific functions

    function addPaypalRest() {
        return;
    }

    function removePaypalRest() {
        return;
    }

    function submitPaypalRest() {
        checkoutFinal();
    }

    function addStripeCard() {
        // Custom styling can be passed to options when creating an Element.
        var style = {
            base: {
                // Add your base input styles here. For example:
                fontSize: '16px',
                color: '#32325d',
            }
        };

        // Create an instance of the card Element.
        if (stripeCard === undefined) {
            stripeCard = elements.create('card', {style: style})
        }
        // Add an instance of the card Element into the `card-element` <div>.
        stripeCard.mount('#stripeCardElement');

        stripeCard.addEventListener('change', function (event) {
            const formGroup = document.getElementById('stripeCardFormGroup')
            const errorElement = document.getElementById('stripeCardErrors');
            if (event.error) {
                errorElement.textContent = event.error.message
                formGroup.classList.add('is-invalid');
                if (!event.complete) {
                    formGroup.classList.remove('is-valid');
                }
            } else {
                errorElement.textContent = ''
                formGroup.classList.remove('is-invalid');
                if (event.complete) {
                    formGroup.classList.add('is-valid');
                }
            }
            //return;
        });
    }

    function removeStripeCard() {
        // var card = settings.card;
        stripeCard.unmount()

        return;
    }

    function submitStripeCard() {
        stripe.createToken(stripeCard).then(function (result) {
            const formGroup = document.getElementById('stripeCardFormGroup')
            const errorElement = document.getElementById('stripeCardErrors');
            if (result.error) {
                formGroup.classList.add('is-invalid');
            } else {
                // Create a token form element.
                stripeTokenHandler(result.token);
                checkoutFinal();
            }
        });
    }

    function addStripeSepa() {
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

        if (stripeIban === undefined) {
            // Create an instance of the iban Element.
            stripeIban = elements.create('iban', {
                style: style,
                supportedCountries: ['SEPA'],
            })
        }

        // Add an instance of the iban Element into the `iban-element` <div>.
        stripeIban.mount('#stripeIbanElement')

        stripeIban.on('change', function (event) {
            var formGroup = document.getElementById('stripeIbanFormGroup')
            var errorElement = document.getElementById('stripeIbanErrors');
            var bankName = document.getElementById('stripeBankName');
            // Handle real-time validation errors from the iban Element.
            if (event.error) {
                errorElement.textContent = event.error.message
                formGroup.classList.add('is-invalid');
                if (!event.complete) {
                    formGroup.classList.remove('is-valid');
                }
            } else {
                errorElement.textContent = ''
                formGroup.classList.remove('is-invalid');
                if (event.complete) {
                    formGroup.classList.add('is-valid');
                }
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
    }

    function removeStripeSepa() {
        stripeIban.unmount()
    }

    function submitStripeSepa(event) {
        var sourceData = {
            type: 'sepa_debit',
            currency: 'eur',
            owner: {
                name: document.querySelector('input[name="Sepa[bankaccountOwner]"]').value,
                // email: document.querySelector('input[name="Sepa[email]"]').value,
                email: 'klaus.mergen@web.de'
            },
            mandate: {
                // Automatically send a mandate notification email to your customer
                // once the source is charged.
                notification_method: 'email',
            }
        };

        // Call `stripe.createSource` with the iban Element and additional options.
        stripe.createSource(stripeIban, sourceData).then(function (result) {
            const formGroup = document.getElementById('stripeIbanFormGroup')
            const errorElement = document.getElementById('stripeIbanErrors');
            if (result.error) {
                // Inform the customer that there was an error.
                errorElement.textContent = result.error.message;
                formGroup.classList.add('is-invalid');
                // stopLoading();
            } else {
                // Send the Source to your server to create a charge.
                formGroup.classList.remove('is-invalid');
                stripeSourceHandler(result.source);
            }
        });
    }

    /* Add client validation to Sepa model fields bankaccountOwner */
    function addSepaValidation(errorMessages) {
        $(checkoutForm).yiiActiveForm('add', {
            id: 'sepa-bankaccountowner',
            name: 'Sepa[bankaccountOwner]',
            container: '.field-sepa-bankaccountowner',
            input: '#sepa-bankaccountowner',
            error: '.invalid-feedback',
            validateOnBlur: false,
            validate: function (attribute, value, messages, deferred, $form) {
                yii.validation.required(value, messages, {message: errorMessages.bankaccountOwner.required});
            }
        })
    }

    /* Remove Sepa model fields from client Validation */
    function removeSepaValidation() {
        $(checkoutForm).yiiActiveForm('remove', 'sepa-bankaccountowner')
    }

    /* Insert the token ID into the form so it gets submitted to the server */
    function stripeTokenHandler(token) {
        let hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        checkoutForm.appendChild(hiddenInput);
    }
    /* Insert the Source ID into the form so it gets submitted to the server. */
    function stripeSourceHandler(source) {
        let hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeSource');
        hiddenInput.setAttribute('value', source.id);
        checkoutForm.appendChild(hiddenInput);
    }

    /*eslint-enable */
    return pub
}(jQuery)


