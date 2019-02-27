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
class StripeWebhookController extends Controller
{
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
     * Test webhooks from Stripe
     * @return Response with statuscode 200
     */
    public function actionAll()
    {
        // Retrieve the request's body and parse it as JSON:
        $input = @file_get_contents('php://input');
        $event_json = json_decode($input);

        // Do something with $event_json
        Yii::info('Klausi send this webhook event: ' . $event_json);
        http_response_code(200); // PHP 5.4 or greater
    }

}
