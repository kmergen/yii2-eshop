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
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use Yii;


/**
 * The Paypal Express Checkout NVP paygate.
 */
class PaygateEcNvp extends BaseObject
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
    public $apiVersion = '204.0';

    /**
     * @var string $apiVersion .
     */
    public $returnUrl;

    /**
     * @var string $apiVersion .
     */
    public $cancelUrl;

    /**
     * @var string $curl .
     */
    public $curl;

    /**
     * @var string $currency .
     */
    public $currency = 'EUR';

    /**
     * @var string $mode possible values are "sandbox" or "live"
     */
    public $mode = 'sandbox';

    /**
     * @var mixed $lastServerResponse Here you can find PayPal response for your last successfull API call
     */
    public $lastServerResponse;

    /**
     * Do the SetExpressCheckout API operation that initiates an Express Checkout transaction
     */
    public function execute($order, $customer, $params = [])
    {
        $req_data = [
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'AUTHORIZATION',
            'PAYMENTREQUEST_0_AMT' => Yii::$app->session->get('orderTotal'),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $this->currency,
            'PAYMENTREQUEST_0_MULTISHIPPING' => 0,
            'RETURNURL' => $this->returnUrl,
            'CANCELURL' => $this->cancelUrl,
            'NOSHIPPING' => 1,
            'PAYMENTREQUEST_0_DESC' => 'Anzeigenoptionen',
            'LOGOIMG' => Yii::$app->urlManager->createAbsoluteUrl('/images/logo.png')
        ];

        $items = $order->orderItems;
        $i = 0;
        foreach ($items as $item) {
            $req_data['L_PAYMENTREQUEST_0_QTY' . $i] = $item->qty;
            $req_data['L_PAYMENTREQUEST_0_AMT' . $i] = \round($item->sell_price, 2);
            $req_data['L_PAYMENTREQUEST_0_DESC' . $i] = $item->title;
            $i++;
        }

        try {
            $res = $this->SetExpressCheckout($req_data);
            if (isset($res['ACK'])) {
                if ($res['ACK'] === 'Success') {
                    // Redirect the user to paypal.com here
                    // We use a payment flow with a Pay Now button. Therefore we add the "useraction" parameter with value "commit" to the url.
                    Yii::$app->getResponse()->redirect('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=' . \urlencode($res['TOKEN']))->send();
                    return;
                } else {
                    // $res['ACK'] === 'Failure'
                    $logMsg = [
                        'Correlation ID' => $res['CORRELATIONID'] ?? '',
                        'Error Code' => $res['L_ERRORCODE0'] ?? '',
                        'Error short Message' => $res['L_SHORTMESSAGE0'] ?? '',
                        'Error long Message' => $res['L_LONGMESSAGE0'] ?? '',
                    ];
                    Yii::error($logMsg, __METHOD__);
                    Yii::$app->session->setFlash('info', Yii::t('eshop', 'paypal.failure.flash.message'));
                }
            } else {
                throw new Exception('Paypal SetExpressCheckout Response Error. Response value: ' . $res);
            }
        } catch (Exception $ex) {
            Yii::error($ex->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('info', Yii::t('eshop', 'paypal.failure.flash.message'));
            return;
        }


    }

    /**
     * The SetExpressCheckout API operation initiates an Express Checkout transaction. Look here to see method parameters: https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
     * @param array $request Array should contain key value pairs defined by PayPal
     * @return array Response from the PayPal saved in the array and returned from the function
     */
    public
    function SetExpressCheckout($request)
    {
        return $this->sendRequest($request, "SetExpressCheckout");
    }

    /**
     * The DoExpressCheckoutPayment API operation completes an Express Checkout transaction. If you set up a billing agreement in your SetExpressCheckout API call, the billing agreement is created when you call the DoExpressCheckoutPayment API operation.The DoExpressCheckoutPayment API operation completes an Express Checkout transaction. If you set up a billing agreement in your SetExpressCheckout API call, the billing agreement is created when you call the DoExpressCheckoutPayment API operation. Look here to see method parameters: https://developer.paypal.com/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/
     * @param array $request Array should contain key value pairs defined by PayPal
     * @return array Response from the PayPal saved in the array and returned from the function
     */
    public
    function DoExpressCheckoutPayment($request)
    {
        return $this->sendRequest($request, "DoExpressCheckoutPayment");
    }

    /**
     * Calles GetExpressCheckoutDetails PayPal API method. This method gets buyer and transaction data. This method WILL NOT- make transaction itself. Look here for method parameters: https://developer.paypal.com/docs/classic/api/merchant/GetExpressCheckoutDetails_API_Operation_NVP/
     * @param array $request Array should contain key value pairs defined by PayPal
     * @return array Response from the PayPal saved in the array and returned from the function
     */
    public
    function GetExpressCheckoutDetails($request)
    {
        return $this->sendRequest($request, "GetExpressCheckoutDetails");
    }

    public
    function DoAuthorization($request)
    {
        return $this->sendRequest($request, "DoAuthorization");
    }

    /* AUTORIZATION AND CAPTURE METHODS */

    /**
     * Captures an authorized payment. You can read more about authorization and payment: https://developer.paypal.com/docs/classic/admin/auth-capture/
     * @param array $request
     * @return array
     */
    public
    function DoCapture($request)
    {
        return $this->sendRequest($request, "DoCapture");
    }

    /**
     * The DoReauthorization API operation reauthorizes an existing authorization transaction. The resulting reauthorization is a new transaction with a new AUTHORIZATIONID. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/DoReauthorization_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function DoReauthorization($request)
    {
        return $this->sendRequest($request, "DoReauthorization");
    }

    /**
     * Void an order or an authorization. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/DoVoid_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function DoVoid($request)
    {
        return $this->sendRequest($request, "DoVoid");
    }

    /**
     * To see method arguments visit this link:
     * @param array $request
     * @return array
     */
    public
    function UpdateAuthorization($request)
    {
        return $this->sendRequest($request, "UpdateAuthorization");
    }

    /* Recurring Payments / Reference Transactions */

    /**
     * The BAUpdate API operation updates or deletes a billing agreement. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/BAUpdate_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function BAUpdate($request)
    {
        return $this->sendRequest($request, "BAUpdate");
    }

    /**
     * The BillOutstandingAmount API operation bills the buyer for the outstanding balance associated with a recurring payments profile. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/BillOutstandingAmount_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function BillOutstandingAmount($request)
    {
        return $this->sendRequest($request, "BillOutstandingAmount");
    }

    /**
     * The CreateBillingAgreement API operation creates a billing agreement with a PayPal account holder. CreateBillingAgreement is only valid for reference transactions. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/CreateBillingAgreement_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function CreateBillingAgreement($request)
    {
        return $this->sendRequest($request, "CreateBillingAgreement");
    }

    /**
     * The DoReferenceTransaction API operation processes a payment from a buyer's account, which is identified by a previous transaction. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/DoReferenceTransaction_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function CreateRecurringPaymentsProfile($request)
    {
        return $this->sendRequest($request, "CreateRecurringPaymentsProfile");
    }

    /**
     * The DoReferenceTransaction API operation processes a payment from a buyer's account, which is identified by a previous transaction. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/DoReferenceTransaction_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function DoReferenceTransaction($request)
    {
        return $this->sendRequest($request, "DoReferenceTransaction");
    }

    /**
     * Obtain information about a recurring payments profile. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/GetRecurringPaymentsProfileDetails_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function GetRecurringPaymentsProfileDetails($request)
    {
        return $this->sendRequest($request, "GetRecurringPaymentsProfileDetails");
    }

    /**
     * The ManageRecurringPaymentsProfileStatus API operation cancels, suspends, or reactivates a recurring payments profile. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/ManageRecurringPaymentsProfileStatus_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function ManageRecurringPaymentsProfileStatus($request)
    {
        return $this->sendRequest($request, "ManageRecurringPaymentsProfileStatus");
    }

    /**
     * The UpdateRecurringPaymentsProfile API operation updates a recurring payments profile. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/UpdateRecurringPaymentsProfile_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function UpdateRecurringPaymentsProfile($request)
    {
        return $this->sendRequest($request, "UpdateRecurringPaymentsProfile");
    }

    /**
     * The RefundTransaction API operation issues a refund to the PayPal account holder associated with a transaction. This API operation can be used to issue a full or partial refund for any transaction within a default period of 60 days from when the payment is received. To see method arguments visit this link: https://developer.paypal.com/docs/classic/api/merchant/RefundTransaction_API_Operation_NVP/
     * @param array $request
     * @return array
     */
    public
    function RefundTransaction($request)
    {
        return $this->sendRequest($request, "RefundTransaction");
    }

    /**
     * This method makes calls PayPal method provided as argument.
     * @param array $requestData
     * @param string $method
     * @return array
     */
    public
    function sendRequest($requestData, $method)
    {
        $requestParameters = [
            "USER" => $this->apiUsername,
            "PWD" => $this->apiPassword,
            "SIGNATURE" => $this->apiSignature,
            "METHOD" => $method,
            "VERSION" => $this->apiVersion,
        ];
        $requestParameters += $requestData;
        $finalRequest = http_build_query($requestParameters);
        $ch = curl_init();
        $this->curl = $ch;

        $curlOptions = $this->getcURLOptions();
        $curlOptions[CURLOPT_POSTFIELDS] = $finalRequest;
        //var_dump($curlOptions);exit;

        curl_setopt_array($ch, $curlOptions);
        $serverResponse = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorNr = curl_errno($ch);
            curl_close($ch);
            $this->lastServerResponse = null;
            throw new Exception('Curl Error: ' . $errorNr);
        } else {
            curl_close($ch);
            $result = [];
            parse_str($serverResponse, $result);
            $this->lastServerResponse = $result;
            return $this->lastServerResponse;
        }
    }

    /**
     * Returns an array of options to initialize cURL
     * @return array
     */
    private
    function getcURLOptions()
    {
        return [
            CURLOPT_URL => 'https://api-3t.sandbox.paypal.com/nvp',
            CURLOPT_VERBOSE => 1,
            CURLOPT_SSLVERSION => 1,
            //Have a look at this: http://stackoverflow.com/questions/14951802/paypal-ipn-unable-to-get-local-issuer-certificate
            //You can download a fresh cURL pem file from here http://curl.haxx.se/ca/cacert.pem
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
            // That is only for Windows PHP
            CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem', //CA cert file
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
        ];
    }

    /**
     * If you want to set cURL with additional parameters, use this function. NOTE: Call this function prior sendRequest method
     * @param int $option
     * @param mixed $value
     */
    public
    function setCURLOption($option, $value)
    {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     * Returns latest result from the PayPal servers
     * @return array
     */
    public
    function getLastServerResponse()
    {
        return $this->lastServerResponse;
    }

}



