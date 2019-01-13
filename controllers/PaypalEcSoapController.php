<?php

namespace kmergen\eshop\controllers;

use Yii;
use kmergen\eshop\models\Payment;
use kmergen\eshop\models\PaymentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PaymentEcController implements the actions for Express Checkout via Paypal SOAP/NVP API.
 */
class PaypalEcSoapController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * A user is redirected to this action after after successfully initiate the express checkout.
     * @return mixed
     */
    public function actionSuccess()
    {
        $token = $_REQUEST['token'];
        $payerId = $_REQUEST['PayerID'];
        $pp = Yii::createObject(Yii::$app->getModule('eshop')->paygates['paypal_ec_soap']);

        try {
            //get the payment informations
            $ecGetRes = $pp->getExpressCheckout($token);
            //we do the payment
            $ecDoRes = $pp->doExpressCheckout($ecGetRes);
        } catch (\Exception $ex) {
            Yii::error($ex->getMessage());
        }


        return $this->redirect(['/anzeigen/bearbeiten', 'id' => '69']);
    }

    /**
     * A user is redirected to this action after after he canceld the the express checkout.
     * @return mixed
     */
    public function actionCancel()
    {
        $redirect = Yii::$app->session->get('checkoutRoute', Yii::$app->getHomeUrl());
        return $this->redirect([$redirect]);
    }
}
