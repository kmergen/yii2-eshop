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
use kmergen\eshop\stripe\models\Card;
use kmergen\eshop\stripe\models\Sepa;
use yii\web\Response;

/**
 * StripeWebhookController implements Stripe webhook events.
 * @see https://stripe.com/docs/webhooks
 */
class StripeWebhooksController extends Controller
{
    /**
     * @var bool
     * Set this to false because stripe can not send the csrf token.
     */
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'all' => ['POST'],
                ],
            ],
        ];
    }
    /**
     * Stripe Webhook payment_intent.succeeded
     * @return Response with statuscode 200
     */
    public function actionPaymentIntentSucceeded()
    {
        // Retrieve the request's body and parse it as JSON:
        $input = @file_get_contents('php://input');
        $event_json = json_decode($input);
        // Do something with $event_json

        Yii::info($event_json, __METHOD__);
        return;
    }

}
