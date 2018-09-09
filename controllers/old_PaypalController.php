<?php

namespace app\modules\eshop\controllers;

use app\modules\eshop\components\paypal\PaypalBasic;
use app\modules\eshop\controllers\CheckoutController;
use yii\filters\VerbFilter;

/**
 * This controller handles the responses from Paypal
 */
class PaypalController extends CheckoutController
{

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                // 'paypal-cancel' => ['post'],
                // 'paypal-basic' => ['post'],
                ],
            ],
        ];
    }

    /**
     * This function is called when the user successfully completed the payment on the paypal page
     */
    public function actionBasic()
    {
        $paypal = new PaypalBasic();
        $details = $paypal->getExpressCheckoutDetails();
        $response = $paypal->doExpressCheckoutPayment($details);

        if (!isset($response['REDIRECTREQUIRED'])) {
            $a = 5;
        } else { // Customer pay with giropay and we must redirect him to paypal to complete his payment with giropay
            $redirectUrl = $paypal->live ? 'https://www.paypal.com/cgi-bin/webscr?cmd=_complete-express-checkout&token=' . $details['TOKEN'] :
                'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_complete-express-checkout&token=' . $details['TOKEN'];

            \Yii::$app->response->redirect($redirectUrl)->send();
            exit();
        }
    }

    /**
     * This function is called when the user cancel the interaction on the paypal page
     */
    public function actionBasicCancel()
    {
        $b = 5;
    }

    /**
     * This function handle the redirect from paypal when the customer has successfully completed the giropay payment 
     */
    public function actionGiropaySuccess()
    {
        //@todo get no response when use sandbox with giropay
        $b = 5;
    }

    /**
     * This function handle the redirect from paypal when the customer has cancel the giropay payment 
     */
    public function actionGiropayCancel()
    {
        //@todo get no response when use sandbox with giropay
        $b = 5;
    }
    
    /**
     * This function handle the redirect from paypal when the customer has switch from giropay to bank transfer (In my profile bank transfer is disabled, so this function is not in use) 
     */
    public function actionGiropayPending()
    {
        //@todo get no response when use sandbox with giropay
    }

}
