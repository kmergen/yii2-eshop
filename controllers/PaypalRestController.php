<?php

namespace kmergen\eshop\controllers;

use kmergen\eshop\interfaces\PaymentEventInterface;
use Yii;
use kmergen\eshop\models\Payment;
use kmergen\eshop\models\PaymentSearch;
use yii\web\Controller;
use yii\base\Exception;
use yii\base\Event;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PaypalRestController implements the actions for Paypal Plus via REST API
 */
class PaypalRestController extends Controller
{
    /**
     * A user is redirected to this action after after successfully initiate the express checkout.
     * @return mixed
     */
    public function actionSuccess()
    {

        $paymentId = $_REQUEST['paymentId'];
        $token = $_REQUEST['token'];
        $payerId = $_REQUEST['PayerID'];
        $pp = Yii::createObject(Yii::$app->getModule('eshop')->paymentMethods['paypal_rest']['paygate']);

        $payment = $pp->doPayment();

//
//        try {
//            //if needed you can get the payment informations. Todo so uncomment the following line
//            //$getRes = $pp->GetExpressCheckoutDetails(['TOKEN' => $token]);
//
//            //we do the payment
//            $req_data = [
//                'TOKEN' => $token,
//                'PAYERID' => $payerId,
//                'AMT' => Yii::$app->session->get('orderTotal'),
//                'CURRENCYCODE' => $pp->currency,
//                'PAYMENTACTION' => 'SALE',
//            ];
//            $doRes = $pp->DoExpressCheckoutPayment($req_data);
//
//            if (isset($doRes['ACK'])) {
//                if ($doRes['ACK'] === 'Success') {
//                    $model = new Payment();
//                    $model->transaction_id = $doRes['TRANSACTIONID'];
//                    $model->order_id = Yii::$app->session->get('orderId');
//                    $model->status = $doRes['PAYMENTSTATUS'];
//                    $model->payment_method = 'paypal_ec_nvp';
//                    $model->data = \serialize($doRes);
//
//                    $model->save(false);
//                    $this->trigger(self::EVENT_PAYMENT_SUCCESS);
//
//                }
//            }
//
//        } catch (Exception $ex) {
//            Yii::error($ex->getMessage(), __METHOD__);
//        }


        return $this->redirect(['/anzeigen/bearbeiten', 'id' => '69']);
    }

    /**
     * A user is redirected to this action after after he canceld the the express checkout in the PayPal window.
     * @return mixed
     */
    public function actionCancel()
    {
        $token = $_GET['token'] ?? 'No token set.';
        Yii::info('User canceled Paypal Express Checkout with token: ' . $token, 'paypal');
        $redirect = Yii::$app->session->get('checkoutRoute', Yii::$app->getHomeUrl());
        //$this->trigger(PaymentEventInterface::EVENT_PAYMENT_CANCELED);
        return $this->redirect([$redirect]);
    }
}
