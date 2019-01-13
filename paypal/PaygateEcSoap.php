<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\paypal;

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;

use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use Yii;


/**
 * The Paypal Express Checkout SOAP paygate.
 */
class PaygateEcSoap extends BaseObject
{
    /**
     * @var string $apiUsername .
     */
    public $apiUsername;

    /**
     * @var string $apiPassword .
     */
    public $apiPassword;

    /**
     * @var string $apiSignature .
     */
    public $apiSignature;

    /**
     * @var string $apiVersion .
     */
    public $apiVersion;

    /**
     * @var string $apiVersion .
     */
    public $returnUrl;

    /**
     * @var string $apiVersion .
     */
    public $cancelUrl;

    /**
     * @var string $currency .
     */
    public $currency = 'EUR';

    /**
     * @var string $mode possible values are "sandbox" or "live"
     */
    public $mode = 'sandbox';

    /**
     * @var array $config
     */
    public $config = [];


    public function init()
    {
        $defaults = [
            'acct1.UserName' => $this->apiUsername,
            'acct1.Password' => $this->apiPassword,
            'acct1.Signature' => $this->apiSignature,
            'mode' => $this->mode,
            'http.ConnectionTimeOut' => 500,
            'http.Retry' => 2,
            'log.LogEnabled' => YII_DEBUG ? 1 : 0,
            'log.FileName' => Yii::getAlias('@runtime/logs/paypal.log'),
            'log.LogLevel' => 'FINE',
            'validation.level' => 'log',
            'cache.enabled' => 'true'
        ];

        $this->config = ArrayHelper::merge($defaults, $this->config);

        // Set file name of the log if present
        if (isset($this->config['log.FileName'])
            && isset($this->config['log.LogEnabled'])
            && ((bool)$this->config['log.LogEnabled'] == true)
        ) {
            $logFileName = \Yii::getAlias($this->config['log.FileName']);
            if ($logFileName) {
                if (!file_exists($logFileName)) {
                    if (!touch($logFileName)) {
                        throw new ErrorException('Can\'t create paypal.log file at: ' . $logFileName);
                    }
                }
            }
            $this->config['log.FileName'] = $logFileName;
        }
    }


    /**
     * The SetExpressCheckout API operation initiates an Express Checkout transaction
     */
    public function execute($order, $customer, $params = [])
    {

        // Payment details
        $paymentDetails = new PaymentDetailsType();


        // Set the order items
        $itemTotalValue = 0;

        $items = $order->orderItems;
        $i = 0;
        foreach ($items as $item) {
            $itemAmount = new BasicAmountType($this->currency, round($item->sell_price, 2));
            $itemTotalValue += $itemAmount->value * $item->qty;
            $itemDetails = new PaymentDetailsItemType();
            $itemDetails->Name = $item->title;
            $itemDetails->Amount = $itemAmount;
            $itemDetails->Quantity = $item->qty;

            $paymentDetails->PaymentDetailsItem[$i] = $itemDetails;

            $i++;
        }

        /**
         * The total cost of the transaction to the buyer. If shipping cost and tax charges are known,
         * include them in this value. If not, this value should be the current subtotal of the order.
         * If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases.
         * If the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment,
         * set this field to 0.
         */
        $paymentDetails->ItemTotal = new BasicAmountType($this->currency, $itemTotalValue);
        $paymentDetails->OrderTotal = new BasicAmountType($this->currency, $itemTotalValue);

        $setECReqDetails = new SetExpressCheckoutRequestDetailsType();
        $setECReqDetails->PaymentDetails[0] = $paymentDetails;
        /*
         * (Required) URL to which the buyer is returned if the buyer does not approve the use of PayPal to pay you. For digital goods, you must add JavaScript to this page to close the in-context experience.
         */
        $setECReqDetails->CancelURL = $this->cancelUrl;
        /*
         * (Required) URL to which the buyer's browser is returned after choosing to pay with PayPal. For digital goods, you must add JavaScript to this page to close the in-context experience.
         */
        $setECReqDetails->ReturnURL = $this->returnUrl;
        /*
         * Determines where or not PayPal displays shipping address fields on the PayPal pages. For digital goods, this field is required, and you must set it to 1. It is one of the following values:
            0 – PayPal displays the shipping address on the PayPal pages.
            1 – PayPal does not display shipping address fields whatsoever.
            2 – If you do not pass the shipping address, PayPal obtains it from the buyer's account profile.
         */
        $setECReqDetails->NoShipping = 1;
        /*
         *  (Optional) Determines whether or not the PayPal pages should display the shipping address set by you in this SetExpressCheckout request, not the shipping address on file with PayPal for this buyer. Displaying the PayPal street address on file does not allow the buyer to edit that address. It is one of the following values:
            0 – The PayPal pages should not display the shipping address.
            1 – The PayPal pages should display the shipping address.
         */
        $setECReqDetails->AddressOverride = 0;
        /*
         * Indicates whether or not you require the buyer's shipping address on file with PayPal be a confirmed address. For digital goods, this field is required, and you must set it to 0. It is one of the following values:
            0 – You do not require the buyer's shipping address be a confirmed address.
            1 – You require the buyer's shipping address be a confirmed address.
         */
        $setECReqDetails->ReqConfirmShipping = 0;

        $setECReqType = new SetExpressCheckoutRequestType();
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
        $setECReq = new SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;
        /*
         * 	 ## Creating service wrapper object
        Creating service wrapper object to make API call and loading
        Configuration::getAcctAndConfig() returns array that contains credential and config parameters
        */
        $paypalService = new PayPalAPIInterfaceServiceService($this->config);

        try {
            /* wrap API method calls on the service object with a try catch */
            $setECResponse = $paypalService->SetExpressCheckout($setECReq);
            if ($setECResponse->Ack === 'Success') {
                // Redirect the user to paypal.com here
                // We use a payment flow with a Pay Now button. Therefore we ad the "useraction" parameter with value "commit" to the url.
                return Yii::$app->controller->redirect('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=' . $setECResponse->Token);
            } elseif ($setECResponse->Ack === 'Failure') {
                foreach ($setECResponse->Errors as $error) {
                    //Log the error
                    Yii::error('Paypal SetExpressCheckout Failure: ' . $error->LongMessage);
                }
                Yii::$app->session->setFlash('error', 'You cannot use Paypal Payment at the moment. Pleas use annother Payment Method');
                $redirect = Yii::$app->session->get('checkoutRoute', Yii::$app->getHomeUrl());
                Yii::$app->response->redirect([$redirect]);
                Yii::$app->end();
            }

        } catch (Exception $ex) {
          $a = 4;
        }
    }

    public function getExpressCheckout($token)
    {
        $getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);

        $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
        $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;

        /*
         * 	 ## Creating service wrapper object
        Creating service wrapper object to make API call and loading
        Configuration::getAcctAndConfig() returns array that contains credential and config parameters
        */
        $paypalService = new PayPalAPIInterfaceServiceService($this->config);

        return $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
    }

    public function doExpressCheckout($ecGetRes)
    {
        $ecGetResDetails = $ecGetRes->GetExpressCheckoutDetailsResponseDetails;

        $paymentDetails = $ecGetResDetails->PaymentDetails[0];

        $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
        $DoECRequestDetails->Token = $ecGetResDetails->Token;
        $DoECRequestDetails->PayerID = $ecGetResDetails->PayerInfo->PayerID;
        $DoECRequestDetails->PaymentAction = 'Sale';
        $DoECRequestDetails->PaymentDetails[0] = $paymentDetails;

        $DoECRequest = new DoExpressCheckoutPaymentRequestType();
        $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;

        $DoECReq = new DoExpressCheckoutPaymentReq();
        $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;

        $paypalService = new PayPalAPIInterfaceServiceService($this->config);
        return $paypalService->DoExpressCheckoutPayment($DoECReq);
    }

}

