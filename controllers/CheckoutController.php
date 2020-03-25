<?php

namespace kmergen\eshop\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use kmergen\eshop\models\CheckoutForm;
use kmergen\eshop\models\Address;
use kmergen\eshop\models\Customer;
use kmergen\eshop\models\Cart;
use kmergen\eshop\models\Order;
use kmergen\eshop\models\Payment;
use kmergen\eshop\models\PaymentStatus;
use kmergen\eshop\events\CheckoutFlowEvent;

class CheckoutController extends Controller
{

    /**
     * @event Event an event that is triggered when the checkout action aborts.
     */
    const EVENT_CHECKOUT_CANCELED = 'checkoutCanceled';

    /**
     * @event  This event is triggered after a checkout is completed.
     */
    const EVENT_CHECKOUT_COMPLETE = 'checkoutComplete';

    /**
     * @event  This event is triggered after a user is redirected to CheckoutController with the paymentmethod "stripe_card".
     * The fullfilment of checkout is done by a Stripe Webhook.
     * This event is mainly used to redirect the user to the right place.
     * Do not checkout fullfilment with this event, remember it is done by webhooks.
     */
    const EVENT_CHECKOUT_COMPLETE_AFTER_STRIPE_WEBHOOK = 'checkoutCompleteAfterStripeWebhook';

    /**
     * @inheritdoc
     */
    public $defaultAction = 'checkout';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'change-payment-method' => ['post'],
                    'update-cart-item' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Checkout for paying ad options
     * If creation is successful, the browser will be redirected to the 'view' page of the model.
     * @param integer $id the Ad ID
     * @return mixed
     */
    public function actionCheckout()
    {
        $module = $this->module;
        $request = Yii::$app->getRequest();
        $post = $request->post();
        $model = new CheckoutForm();

        $modelLoaded = $model->load($post);

        if (($cart = Cart::getCurrentCart()) === null || empty($cart->items)) {
            if (!$modelLoaded || ($modelLoaded && $model['paymentMethod'] !== 'stripe_card')) {
                Yii::$app->session->setFlash('info', Yii::t('eshop', 'Your cart is empty.'));
                return $this->goBack();
            }
        }

        // Only for testing
        $paypalQuickApproval = false;

        if ($modelLoaded) {
            if (!$model->checkoutCanceled) {
                if ($model->validate()) { // We have client validation enabled. The model should validate, if not there is a manipulation on user input and we go back to returnUrl
                    if ($model['paymentMethod'] === 'stripe_card') {
                        // We do not any fullfilment here, that will be done by the Stripe Webhooks (succeed or canceled).
                        // We use it only to redirect the user.
                        $card = new \kmergen\eshop\stripe\models\Card();
                        $card->load($post);
                        $paygate = $card->paygate;
                        $intent = $paygate->retrieveIntent($card->intentId);
                        $event = new CheckoutFlowEvent();
                        $event->cartId = $intent->metadata->cart_id;
                        $event->paymentMethod = $model['paymentMethod'];
                        $this->trigger(self::EVENT_CHECKOUT_COMPLETE, $event);
                        if (!empty($event->flash)) {
                            Yii::$app->session->setFlash($event->flash[0], $event->flash[1]);
                        }
                        return ($event->redirectUrl === null) ? $this->goBack() : $this->redirect($event->getRedirectUrl());
                    } elseif ($model->paymentMethod === 'stripe_sepa') {
                        // Cannot use this payment method. Just wait until Stripe has unlocked this feature.
                        $sepa = new \kmergen\eshop\stripe\models\Sepa();
                        if ($sepa->load($post) && $sepa->validate()) {
                            $sepa->createCharge($cart);
                        } else {
                            Yii::$app->session->setFlash(Yii::t('eshop', 'At the moment you cannot do a stripe sepa payment. Please try annother payment method.'));
                        }
                    } elseif ($paypalQuickApproval) {
                        $event = $this->testCheckoutFlow('paypal_rest');
                        return $this->redirect($event->getRedirectUrl());
                    } elseif ($model->paymentMethod === 'paypal_rest') {
                        $paygate = Yii::createObject(
                            $module->paymentMethods[$model['paymentMethod']]['paygate']
                        );
                        $paygate->execute(Cart::getCurrentCart());
                    }
                } else { // Model not validate
                    Yii::$app->session->setFlash('warning', Yii::t('flash.checkoutModel.notValidate.OnServerSide'));
                    return $this->goBack();
                }
            } else {
                Yii::info('Checkout canceled with Cancel Button', __METHOD__);
                $event = new CheckoutFlowEvent();
                $this->trigger(self::EVENT_CHECKOUT_CANCELED, $event);
                return ($event->redirectUrl === null) ? $this->goBack() : $this->redirect($event->getRedirectUrl());
            }
            $model->paymentMethod = null;
        } else {
            if ($cart->customer_id === null) {
                if (($customer = Customer::find()->where(['user_id' => Yii::$app->user->id])->one()) !== null) {
                    $customer->email = Yii::$app->user->getIdentity()->email;
                    $customer->updateAttributes(['email']);
                    $cart->updateAttributes(['customer_id' => $customer->id]);

                    // Look if the customer has an invoice_address. Then we use the invoice_address of the last order
                    $lastOrderWithAddress = Order::find()->asArray()->with('eshop_address')->where("customer_id=$customer->id AND invoice_address_id IS NOT NULL")
                        ->orderBy('created_at DESC')->limit(1)->all();
                    if (!empty($lastOrderWithAddress)) {
                        $address = Address::findOne($lastOrderWithAddress['eshop_address']['id']);
                    } else {
                        // $address = new Address();
                    }
                } else {
                    $customer = new Customer();
                    $customer->user_id = Yii::$app->user->id;
                    $customer->email = Yii::$app->user->getIdentity()->email;
                    $customer->save(false);
                    $cart->updateAttributes(['customer_id' => $customer->id]);
                    //$address = new Address();
                }
            }
        }
        return $this->render('checkout', [
            'cart' => $cart,
            'module' => $module,
            'model' => $model,
            //'address' => $address,
        ]);
    }

    /**
     * The checkout is completed and the user see the Thank you page
     * @param $id integer The order Id of this Checkout Flow
     * @return resource
     */
    public function actionComplete($id)
    {
        $order = Order::findOne($id);
        return $this->render('complete', ['order' => $order]);
    }

    /**
     * A user is redirected to this action after successfully initiate the paypal checkout.
     * This means the user has clicked the "Pay now" button in the PayPal window.
     * We keep the function here in this controller as initiator of the checkout flow.
     * This function initiate:
     *  - Payment creation
     *  - Payment initiate order creation
     *
     * @return mixed
     */
    public function actionPaypalApproval()
    {
        try {
            $transaction = Payment::getDb()->beginTransaction();
            $paygate = Yii::createObject($this->module->paymentMethods['paypal_rest']['paygate']);
            $paypalPayment = $paygate->doPayment();

            if ($paypalPayment->state === 'approved' || $paypalPayment->state === 'success') {
                $paypalTransaction = $paypalPayment->transactions[0];
                $relatedResources = $paypalTransaction->getRelatedResources();
                $relatedResource = $relatedResources[0];
                $sale = $relatedResource->getSale();
                // Create new Payment Model
                $cart = Cart::findOne($paypalTransaction->getCustom());
                if ($cart->status === Cart::STATUS_COMPLETE) {
                    // Do not create Payment and order for a complete cart.
                    Yii::error("Cart $cart->id has already status " . Cart::STATUS_COMPLETE, __METHOD__);
                    return $this->goBack();
                }
                $payment = new Payment();
                $payment->cart_id = $cart->id;
                $payment->transaction_id = $sale->getId();
                $payment->payment_method = 'paypal_rest';
                $payment->status = Payment::STATUS_COMPLETE;
                $payment->data = \serialize($paypalPayment);
                $payment->save();
                $order = Order::createOrder($payment, $cart);
                $cart->updateAttributes(['status' => Cart::STATUS_COMPLETE]);
                $event = $this->completeCheckout($payment, $order);
                $transaction->commit();
                // Remove Cart from Session
                Cart::removeCartFromSession();
                return $this->redirect($event->getRedirectUrl());
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('eshop', 'PayPal cannot execute the Payment. Pleas choose another Payment method.'));
                return $this->redirect([$this->defaultAction]);
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
        }
    }

    /**
     * This function is only for testing a checkout flow for a payment method.
     * Here we start by inserting the payment and proof the checkout flow.
     */
    public function testCheckoutFlow($paymentMethod)
    {
        try {
            $model = new Payment();
            $cart = Cart::getCart();
            $model->cart_id = $cart->id;
            $model->transaction_id = \uniqid();
            $model->payment_method = $paymentMethod;
            $model->status = Payment::STATUS_COMPLETE;
            $model->save();
            return $this->completeCheckout($model);
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
        }
    }

    /**
     * A user is redirected to this action after he canceld the the checkout in the PayPal window.
     * @return mixed
     */
    public function actionPaypalCancel()
    {
        $token = $_GET['token'] ?? 'No token set.';
        Yii::info('User canceled Paypal Express Checkout with token: ' . $token, __METHOD__);
        //$this->trigger(PaymentEventInterface::EVENT_PAYMENT_CANCELED);
        return $this->redirect([$this->defaultAction]);
    }

    /**
     * Complete the Checkout Flow
     * This is for "paypal_rest" and "stripe_sepa" payment methods.
     * @param object kmergen\eshop\models\Payment
     */
    private function completeCheckout($payment, $order)
    {
        $event = new CheckoutFlowEvent();
        $event->cartId = $payment->cart_id;
        $event->paymentMethod = $payment->payment_method;
        $event->order = $order;
        $event->payment = $payment;
        $event->redirectUrl = ['complete', 'id' => $order->id];
        //Yii::endProfile('CheckoutFlow');
        $this->trigger(self::EVENT_CHECKOUT_COMPLETE, $event);
        if (!$event->emailSent) {
            static::sendOrderConfirmationMail($order);
        }
        if (empty($event->flash)) {
            $event->flash[0] = 'success';
            $event->flash[1] = Yii::t('eshop', 'view.checkout.complete.flash.success');
        }
        Yii::$app->session->setFlash($event->flash[0], $event->flash[1]);
        return $event;
    }

    public static function sendOrderConfirmationMail($order)
    {
        $module = Yii::$app->getModule('eshop');
        $customer = $order->customer;
        $mailer = Yii::$app->getMailer();
        $mailer->viewPath = '@kmergen/eshop/mail';
        $mailer->compose('order-confirmation-html', [
            'module' => $module,
            'order' => $order,
            'customer' => $customer
        ])
            ->setFrom($module->shopEmail)
            ->setTo($customer->email)
            ->setSubject(Yii::t('eshop', 'Your order from our Shop'))
            ->send();
    }

}
