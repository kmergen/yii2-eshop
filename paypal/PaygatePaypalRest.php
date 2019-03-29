<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\paypal;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Presentation;
use PayPal\Api\RedirectUrls;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;
use PayPal\Api\FlowConfig;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use yii\base\Component;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\base\Exception;
use Yii;
use kmergen\eshop\components\PaymentEvent;
use kmergen\eshop\models\Cart;

class PaygatePaypalRest extends Component
{
    public $clientId;
    public $clientSecret;
    public $currency = 'EUR';
    public $config = [];

    public $returnUrl;
    public $cancelUrl;

    private $_apiContext;

    // API Context
    // Use an ApiContext object to authenticate API calls.
    // The clientId and clientSecret for the OAuthTokenCredential class
    // can be retrieved from developer.paypal.com
    function init()
    {
        // Set _apiContext
        $this->_apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->clientId,
                $this->clientSecret
            )
        );

        // Config _apiContext
        $this->_apiContext->setConfig(
            ArrayHelper::merge([
                'mode' => 'sandbox',
                'http.ConnectionTimeOut' => 30,
                'http.Retry' => 1,
                'log.LogEnabled' => YII_DEBUG ? 1 : 0,
                'log.FileName' => Yii::getAlias('@runtime/logs/paypal.log'),
                'log.LogLevel' => 'ERROR',
                'validation.level' => 'log',
                'cache.enabled' => 'true'
            ], $this->config)
        );

        // Write Log
        if (isset($this->config['log.FileName']) && isset($this->config['log.LogEnabled']) && ((bool)$this->config['log.LogEnabled'] == true)) {
            $logFileName = Yii::getAlias($this->config['log.FileName']);
            if ($logFileName) {
                if (!file_exists($logFileName)) {
                    if (!touch($logFileName)) {
                        throw new Exception('Can\'t create paypal.log file at: ' . $logFileName);
                    }
                }
            }
            $this->config['log.FileName'] = $logFileName;
        }
    }

    /**
     * Creates a new PayPal Payment. This function is called from Checkout Controller if the Payment Method is paypal_rest.
     * @param $cart
     * @param $customer
     * @param null $params
     * @return \yii\web\Response
     */
    public function execute($cart, $customer, $params = null)
    {
        // Create the payment
        // Set the web-profile-experience @todo Set some profiles permanent.
        $inputFields = new InputFields();
        $inputFields->setAllowNote(true)
            ->setNoShipping(1); // Important step Don't show shipping address
        //   ->setAddressOverride(0);

        $presentation = new Presentation();
        $presentation->setBrandName(Yii::$app->name);
        $presentation->setLogoImage(Yii::$app->urlManager->createAbsoluteUrl('/images/logo.png'));
        $presentation->setLocaleCode('DE');

        $flowConfig = new FlowConfig();
        $flowConfig->setUserAction('commit');

        $webProfile = new WebProfile();
        $webProfile->setName(uniqid())
            ->setFlowConfig($flowConfig)
            ->setInputFields($inputFields)
            ->setPresentation($presentation)
            ->setTemporary(true);

        $createProfile = $webProfile->create($this->_apiContext);

        // Set the payer
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $cartList = [];
        foreach ($cart->items as $cartItem) {
            $item = new Item();
            $item->setName($cartItem->title)
                ->setCurrency($this->currency)
                ->setQuantity($cartItem->qty)
                ->setPrice(round($cartItem->sell_price, 2));
            $cartList[] = $item;
        }
        $itemList = new ItemList();
        $itemList->setItems($cartList);

        $details = new Details();
        //$details->setShipping('0.00');
        $details->setSubtotal($cart->total);
        $amount = new Amount();
        $amount->setCurrency($this->currency)
            ->setTotal($cart->total)
            ->setDetails($details);

        // Set the transaction
        $transaction = new Transaction();
        // $invoiceNumber = $this->config['mode'] === 'sandbox' ? \uniqid() : $cart->id;
        $custom = $cart->id;
        $transaction->setAmount($amount)
            ->setItemList($itemList)
           // ->setInvoiceNumber($invoiceNumber) // We set the Cart Id as our invoice Number. Paypal only allow to use this number once.
            ->setCustom($custom);
            // ->setDescription('Anzeigeoptionen')


        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->returnUrl)
            ->setCancelUrl($this->cancelUrl);

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction])
            ->setExperienceProfileId($createProfile->getId());

        try {
            $payment->create($this->_apiContext);
            //return $payment;
            return Yii::$app->controller->redirect($payment->getApprovalLink());
        } catch (PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
            // Yii::$app->response->data = $ex->getData();
        }
    }

    /**
     * Execute the created PayPal Payment.
     * @return Payment
     */
    public function doPayment()
    {
        $paymentId = $_REQUEST['paymentId'];
        $payment = Payment::get($paymentId, $this->_apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($_REQUEST['PayerID']);
        $transaction = $payment->transactions[0];
        $execution->addTransaction($transaction);
        return $payment->execute($execution, $this->_apiContext);
    }
}
