<?php

namespace kmergen\eshop\controllers;

use kmergen\eshop\interfaces\PaymentEventInterface;
use Yii;
use yii\web\Controller;
use yii\base\Exception;
use yii\base\Event;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use kmergen\eshop\models\Cart;
use kmergen\eshop\stripe\models\Card;
use kmergen\eshop\stripe\models\Sepa;


/**
 * StripeController implements the actions for Stripe
 */
class StripeController extends Controller
{
    /**
     * Render the stripe card pane and returns it via ajax.
     * @return string
     */
    public function actionCardPane()
    {
        if (!Yii::$app->getRequest()->getIsAjax()) {
            return MethodNotAllowedHttpException();
        }
        $model = new Card();
        $intent = $model->paygate->getIntent();
        $model->intentId = $intent->id;
        $model->clientSecret = $intent->client_secret;
        if (YII_DEBUG) {
            $model->cardHolderName = 'Klaus Mergen';
        }

        $form = new \yii\bootstrap4\ActiveForm();
        $form->enableClientScript = false;

        $data = [];
        $data['html'] = $this->renderAjax('@kmergen/eshop/stripe/views/card_pane', [
            'model' => $model,
            'form' => $form,
        ]);
        $data['errorMessages'] = [
            'cardHolderName' => [
                'required' => Yii::t('eshop', 'Please enter here the {0}', $model->getAttributeLabel('cardHolderName'))
            ],
        ];

        return $this->asJson($data);
    }

    /**
     * Render the stripe sepa pane and returns it via ajax.
     * @return string
     */
    public function actionSepaPane()
    {
        if (!Yii::$app->getRequest()->getIsAjax()) {
            return MethodNotAllowedHttpException();
        }

        $form = new \yii\bootstrap4\ActiveForm();
        $form->enableClientScript = false;

        $model = new Sepa();
        $cart = Cart::getCurrentCart();
        if (($customer = $cart->customer) !== null) {
            $model->email = $customer->email;
        }
        if (($invoiceAddress = $cart->invoiceAddress) !== null) {
            $model->bankaccountOwner = $invoiceAddress->fullname;
        }


        $data = [];
        $data['html'] = $this->renderAjax('@kmergen/eshop/stripe/views/sepa_pane', [
            'model' => $model,
            'form' => $form,
        ]);

        $data['errorMessages'] = [
            'bankaccountOwner' => [
                'required' => Yii::t('eshop', 'Please enter here the {0}', $model->getAttributeLabel('bankaccountOwner'))
            ],
            'email' => [
                'required' => Yii::t('eshop', 'Please enter here your email address'),
                'email' => Yii::t('eshop', 'The entered email address does not seem to be correct')
            ],
        ];

        return $this->asJson($data);
    }

}
