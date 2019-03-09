<?php

namespace kmergen\eshop\controllers;

use DeepCopy\f001\A;
use kmergen\eshop\components\PaymentEvent;
use kmergen\eshop\interfaces\PaymentEventInterface;
use kmergen\eshop\models\Shipping;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use kmergen\eshop\helpers\Cart;
use yii\web\HttpException;
use kmergen\eshop\models\CheckoutForm;
use kmergen\eshop\models\Address;
use kmergen\eshop\models\Customer;
use kmergen\eshop\models\Order;
use kmergen\eshop\models\OrderItem;
use kmergen\eshop\stripe\PaygateStripe;
use kmergen\eshop\models\Payment;
use kmergen\eshop\models\PaymentStatus;


class CheckoutController extends Controller
{

    /**
     * @event Event an event that is triggered when the checkout action aborts.
     */
    const EVENT_CHECKOUT_ABORT = 'checkoutCanceled';

    /**
     * @event Event an event that is triggered after the payment.
     */
    const EVENT_AFTER_PAYMENT = 'afterPayment';

    /**
     * array The params which we can attach to an event handler.
     */
    public $params = [];

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
                    'update-cart-item' => ['post'],
                    'change-payment-method' => ['post'],
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
        if (($cart = Order::getCurrentCart()) === null) {
            Yii::$app->session->setFlash('info', Yii::t('eshop', 'Your cart is empty.'));
            return $this->goBack();
        }

        $module = $this->module;
        $request = Yii::$app->getRequest();
        $post = $request->post();
        $model = new CheckoutForm();
       // $isOrderWithShipping = $cartContent['shipping'];
        $paymentModel = null;

        if (($customer = Customer::find()->where(['user_id' => Yii::$app->user->id])->one()) !== null) {
            $customer->email = Yii::$app->user->getIdentity()->email;
            $customer->updateAttributes(['email']);

            // Look if the customer has an invoice_address. Then we use the invoice_address of the last order
            $lastOrderWithAddress = Order::find()->asArray()->with('eshop_address')->where("customer_id={$customer->id} AND invoice_address_id IS NOT NULL")->orderBy('created_at DESC')->limit(1)->all();
            if (!empty($lastOrderWithAddress)) {
                $address = Address::findOne($lastOrderWithAddress['eshop_address']['id']);
            } else {
                $address = new Address();
            }
        } else {
            $customer = new Customer();
            $customer->user_id = Yii::$app->user->id;
            $customer->email = Yii::$app->user->getIdentity()->email;
            $address = new Address();
        }

        if (Cart::getOrderId() === null) {
            $order = $this->createOrder($cartContent, $customer->id);
            Cart::setOrderId($order->id);
        } else {
            $order = Order::find()->with('order_items')->where(['order_id' => Cart::getOrderId()]);
        }


        if ($model->load($post)) {
            if (!$model->checkoutCanceled) {
                if ($model->validate()) { // We have client validation enabled. The model should validate, if not there is a manipulation on user input and we go back to returnUrl
                    $address->load($post);
                    $address->save();

                    if ($isOrderWithShipping) {
                        $shipping = new Shipping();
                        // @todo go further with the shipping model and save it.
                    }

                    //Do the payment
                    if ($model['paymentMethod'] !== 'stripe_card') {
                        $paygate = Yii::createObject(
                            $module->paymentMethods[$model['paymentMethod']]['paygate']
                        );
                        $paygateParams = [];
                        $paygate->on($paygate::EVENT_PAYMENT_DONE, [$this, 'paymentDone']);
                        $paygate->execute($order, $customer, $paygateParams);
                    }

                } else { // Model not validate
                    Yii::$app->session->setFlash('warning', Yii::t('flash.checkoutModel.notValidate.OnServerSide'));
                    return $this->redirect([Yii::$app->session->get(Cart::LAST_URL)]);
                }
            } else {
                Yii::$app->session->set(Cart::IS_CHECKOUT_CANCELED, true);
                return $this->redirect([Yii::$app->session->get(Cart::LAST_URL)]);
            }
            $model->paymentMethod = null;
        }

        return $this->render('checkout', [
            'cartContent' => $cartContent,
            'module' => $module,
            'model' => $model,
            'paymentModel' => $paymentModel,
            'address' => $address,
        ]);
    }

    /**
     * A user is redirected to this action after after successfully initiate the paypal checkout.
     * This means the user has clicked the "Pay now" button in the PayPal window.
     * @return mixed
     */
    public function actionPaypalSuccess()
    {
        $paymentId = $_REQUEST['paymentId'];
        $token = $_REQUEST['token'];
        $payerId = $_REQUEST['PayerID'];
        $paygate = Yii::createObject($this->module->paymentMethods['paypal_rest']['paygate']);
        $paygate->on($paygate::EVENT_PAYMENT_DONE, [$this, 'paymentDone']);
        $payment = $paygate->doPayment();
    }

    /**
     * A user is redirected to this action after he canceld the the checkout in the PayPal window.
     * @return mixed
     */
    public function actionPaypalCancel()
    {
        $token = $_GET['token'] ?? 'No token set.';
        Yii::info('User canceled Paypal Express Checkout with token: ' . $token, 'paypal');
        //$this->trigger(PaymentEventInterface::EVENT_PAYMENT_CANCELED);
        return $this->redirect([$this->defaultAction]);
    }


    /**
     * This method is called after the payment is done.
     * @param $event kmergen\eshop\components\PaymentEvent
     * @return mixed
     */
    public function paymentDone($event)
    {
        $info = $event->payment;

        // Create the Payment Model
        $payment = new Payment();
        $paymentStatus = new PaymentStatus();
        $payment->load($info);
        $payment->insert(false);

        // Create PaymentStatus Model
        $paymentStatus = new PaymentStatus();
        $paymentStatus->payment_id = $payment->id;
        $paymentStatus->status = $payment->status;
        $paymentStatus->insert(false);


    }

    /**
     * Creates a new Order
     * @return object kmergen\eshop\models\Order
     */
    protected function createOrder($cartContent, $customerId)
    {
        $order = new Order();
        $order->customer_id = $customerId;
        $order->total = $cartContent['total'];
        $order->ip = Yii::$app->getRequest()->getRemoteIP();
        $order->status = Order::STATUS_PENDING;
        $order->save();
        foreach ($cartContent['items'] as $v) {
            if ($v['qty'] > 0) {
                $orderItem = new OrderItem();
                $orderItem->product_id = $v['id'];
                $orderItem->title = $v['title'];
                $orderItem->sku = $v['sku'];
                $orderItem->qty = $v['qty'];
                $orderItem->sell_price = $v['sell_price'];
                $orderItem->link('order', $order);
            }
        }
        return $order;
    }
}
