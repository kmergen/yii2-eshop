<?php

namespace app\modules\eshop\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\modules\eshop\helpers\Cart;
use yii\web\HttpException;
use app\modules\eshop\models\CheckoutForm;
use app\modules\eshop\models\Address;
use app\modules\eshop\models\Order;

class CheckoutController extends Controller
{

    /**
     * @event Event an event that is triggered when the checkout action aborts.
     */
    const EVENT_CHECKOUT_ABORT = 'checkoutAbort';

    /**
     * @event Event an event that is triggered after the payment.
     */
    const EVENT_AFTER_PAYMENT = 'afterPayment';

    /**
     * array The params which we can attach to an event handler.
     */
    public $params = [];

    /**
     * integer The user id:
     */
    private $_uid;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'update-cart-item' => ['post'],
                    'change-payment-method' => ['post'],
                ],
            ],
        ];
    }

    /**
     * This action handles the checkout
     */
    public function actionIndex()
    {
        if (!isset($_GET['status'])) { //Before the payment is done

            if (!Yii::$app->user->isGuest) {
                $this->_uid = Yii::$app->user->id;
            } else {
                $this->_uid = Yii::$app->session->get('checkoutUid');
            }

            if ($this->_uid === null) {
                throw new HttpException(403, 'No user id set');
            }


            //Abbruch der Bestellung
            if (isset($_POST['btnAbort'])) {
                $this->checkoutAbort();
                $returnUrl = Yii::$app->session->get('checkoutReturnUrl');
                if ($returnUrl !== null) {
                    Yii::$app->session->remove('checkoutReturnUrl');
                    return $this->redirect($returnUrl);
                }
                return $this->render('abort');
            }


            $cartContent = Cart::getCartContent();

            $address = new Address();
            $checkoutForm = new CheckoutForm();
            $paymentModel = null;

            if (isset($_POST['btnSave'])) {
                //Check if there is a ArticleOld in the cart
                if (!isset($_POST['articles'])) {
                    $this->checkoutAbort();
                    return $this->render('abort');
                }

                $paymentModelValidate = false;

                $checkoutForm->attributes = $_POST['CheckoutForm'];
                if ($checkoutForm->validate(['paymentMethod'])) {
                    $m = 'app\modules\eshop\models\\' . $checkoutForm->paymentMethod . 'Form';
                    $paymentModel = new $m;
                    if ($paymentModel->load($_POST) && $paymentModel->validate()) {
                        $paymentModelValidate = true;
                    }
                }

                $address->attributes = $_POST['Address'];

                if ($address->validate() && $paymentModelValidate && $checkoutForm->validate()) {

                    $order = new Order;
                    $order->uid = $this->_uid;
                    $order->billing_firstname = $address->firstname;
                    $order->billing_lastname = $address->lastname;
                    $order->billing_street1 = $address->street1;
                    $order->billing_postcode = $address->postcode;
                    $order->billing_city = $address->city;
                    $order->billing_country = $address->country;
                    foreach ($cartContent['items']as $v) {
                        if ($v['qty'] > 0) {
                            $order->orderArticles[] = $v;
                        }
                    }
                    $order->total = $cartContent['total'];
                    $order->host = Yii::$app->getRequest()->getHostInfo();
                    $order->status = 'process';

                    if ($order->save()) {
                        $this->params['orderId'] = $order->id;
                        //Do the payment over the paygate
                        $paygateClass = $this->module->paymentMethods[$checkoutForm['paymentMethod']]['paygateClass'];
                        $paygate = new $paygateClass;
                        $paygate->execute($order, $paymentModel);
                        //when the payment method is Debit than we come back to here
                        $this->params['status'] = $paygate->status;
                        return $this->complete();
                    }
                }
            }
            return $this->render('index', [
                    'address' => $address,
                    'cartPane' => Cart::renderCartPane($cartContent),
                    'checkoutForm' => $checkoutForm,
                    'paymentModel' => $paymentModel,
                    //'paymentMethodPane' => $renderPaymentModel ? $paymentModel->renderPane($paymentModel) : '',
            ]);
        } else { //After the payment is done
            $this->params['status'] = $_GET['status'];
            $this->params['orderId'] = $_GET['orderId'];
            $this->complete();
        }
    }

    /**
     * This function is called to complete the checkout
     * @param string paymentMethod
     * @return no return
     */
    public function complete()
    {

        $this->afterPayment();
        $returnUrl = Yii::$app->session->get('checkoutReturnUrl');
        if ($this->params['status'] === 'success') {
            $this->destroySessionVars();
            if ($returnUrl !== null) {
                return $this->redirect($returnUrl);
            }
            return $this->render('success');
        } else {
            if ($returnUrl !== null) {
                $this->destroySessionVars();
                return $this->redirect($returnUrl);
            }
            return $this->render('payment_abort');
        }
    }

    /**
     * This function is called via ajax when we change the payment method
     * @param string paymentMethod
     * @return string html the rendered paymentMethodPane
     */
    public function actionChangePaymentMethod()
    {
        $m = 'app\modules\eshop\models\\' . $_POST["paymentMethod"] . 'Form';
        $model = new $m;
        $pane = strtolower($_POST['paymentMethod']) . '_pane';
        echo $this->renderPartial($pane, ['model' => $model]);
        //echo $model->renderPane();
    }

    /**
     * This function is called via ajax when we change the qty checkbox or the qty textfield
     * @param string ArticleId in the form ArticleId e.g Article_400
     * @param qty the new qty
     * @return string html the rendered cartPane
     */
    public function actionUpdateCartItem()
    {
        $ArticleId = $_POST['ArticleId'];
        $qty = $_POST['qty'];
        $cart = Yii::$app->session[Cart::CART_ID];
        $_SESSION[Cart::CART_ID][$articleId] = $qty;
        $cartContent = Cart::getCartContent();
        $html = Cart::renderCartPane($cartContent);
        echo $html;
    }

    /**
     * This function unset the checkout session vars.
     */
    public function destroySessionVars()
    {
        Yii::$app->session->remove(Cart::CART_ID);
        Yii::$app->session->remove('checkoutUid');
        Yii::$app->session->remove('checkoutReturnUrl');
    }

    /**
     * This function is called on a checkout abort
     */
    public function checkoutAbort()
    {
        //We set here the Ad event handler but we must find a better way to do this
        $this->on(self::EVENT_CHECKOUT_ABORT, ['app\models\Ad', 'handleCheckoutAbort'], $this->params);

        $this->trigger(self::EVENT_CHECKOUT_ABORT);
    }

    /**
     * This function is called after payment
     */
    public function afterPayment()
    {
        //We set here the Ad event handler but we must find a better way to do this
        $this->on(self::EVENT_AFTER_PAYMENT, ['app\models\Ad', 'handleAfterPayment'], $this->params);

        $this->trigger(self::EVENT_AFTER_PAYMENT);
    }

}
