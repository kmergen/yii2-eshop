<?php

namespace kmergen\eshop\controllers;

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
    const EVENT_STRIPE_PAYMENT_INTENT = 'stripePaymentIntent';

    /**
     * @var bool
     * Set this to false because stripe can not send the csrf token.
     */
    public $enableCsrfValidation = false;

    /**
     * @var object The data we send with the event. We don't create an extra event.
     * The handler can use the data with $event->sender->data.
     */
    public $data;

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
     * Test method only for testing on localhost without stripe signature because data came from ngrok
     * Handle Webhooks payment_intent.succeeded and payment_intent.payment_failed
     * Return a response with statusCode 200, othwise 400.
     * @return object
     */
    public function actionTestPaymentIntent()
    {
        $payload = @file_get_contents('php://input');
        $this->data = \json_decode($payload);
        $intent = $this->data->data->object;


        $response = Yii::$app->response;
        $response->setStatusCode(200);
        $response->send();

        $path = Yii::$app->basePath . '/runtime/test.txt';
        $text['title'] = "\nWebhook empfangen am: " . Yii::$app->formatter->asDatetime(\time()) . "\n";
        $text['payload'] = $payload;
        \file_put_contents($path, $text, FILE_APPEND);


        $msg['title'] = "Stripe Webhook {$this->data->type} mit der Id {$intent->id} empfangen";
        if ($this->data->type == "payment_intent.succeeded") {

        } elseif ($this->data->type == "payment_intent.payment_failed") {
            $msg['last_payment_error'] = $intent->last_payment_error ? $intent->last_payment_error->message : '';
        }

        Yii::info(\json_encode($msg), __METHOD__);

        $event = new Event();
        $this->trigger(self::EVENT_STRIPE_PAYMENT_INTENT, $event);
    }

    /**
     * Handle Webhooks payment_intent.succeeded and payment_intent.payment_failed
     * Return a response with statusCode 200, otherwise 400.
     * @return object
     */
    public function actionPaymentIntent()
    {
        $response = Yii::$app->response;
        $endpoint_secret = 'whsec_1J45nsShJmk9l7xzTdXLu1TS0gvP5enO';
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $webhookEvent = null;

        try {
            $webhookEvent = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Yii::$app->response->setStatusCode(400);
            $response->send();
        } catch (\Stripe\Error\SignatureVerification $e) {
            // Invalid signature
            Yii::$app->response->setStatusCode(400);
            $response->send();
        }

        Yii::$app->response->setStatusCode(200);
        $response->send();

        $this->data = $webhookEvent->data;
        $intent = $this->data->data->object;

        $msg['title'] = "Stripe Webhook {$this->data->type} mit der Id {$intent->id} empfangen";
        if ($this->data->type == "payment_intent.succeeded") {

        } elseif ($this->data->type == "payment_intent.payment_failed") {
            $msg['last_payment_error'] = $intent->last_payment_error ? $intent->last_payment_error->message : '';
        }

        Yii::info(\json_encode($msg), __METHOD__);

        $event = new Event();
        $this->trigger(self::EVENT_STRIPE_PAYMENT_INTENT, $event);
    }

}
