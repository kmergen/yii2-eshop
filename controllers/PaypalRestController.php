<?php

namespace kmergen\eshop\controllers;

use Yii;
use yii\web\Controller;
use yii\base\Exception;
use kmergen\eshop\models\Payment;
use kmergen\eshop\components\PaymentEvent;
use kmergen\eshop\paypal\PaygatePaypalRest;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PaypalRestController implements the actions for Paypal via REST API
 */
class PaypalRestController extends Controller
{
    /**
     * Render the paypal rest pane and returns it via ajax.
     * @return string
     */
    public function actionPane()
    {
        if (!Yii::$app->getRequest()->getIsAjax()) {
            return MethodNotAllowedHttpException();
        }
        $form = new \yii\bootstrap4\ActiveForm();
        $form->enableClientScript = false;

        $data = [];
        $data['html'] = $this->renderAjax('@kmergen/eshop/paypal/views/rest_pane');
        $data['errorMessages'] = [];

        return $this->asJson($data);
    }

}
