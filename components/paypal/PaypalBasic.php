<?php

namespace app\modules\eshop\components\paypal;

use app\modules\eshop\Module;
use app\modules\eshop\components\paypal\Paypal;
use yii\base\Exception;

/**
 * Paypal Basic Payment Method
 * This is the normal paypal process with the three API calls setExpressCheckout, getExpressCheckoutDetails and doExpressCheckoutPayment
 */
class PaypalBasic extends Paypal
{

    /**
     * This function is called from PaypalController when the transfer is complete
     */
    public function complete()
    {
        $this->orderId = $_POST['orders_id'];
        $this->tid = $_POST['tid'];
        $this->setPaymentStatus($_POST['status']);
        $this->setOrderStatus();

        if ($this->checkHash($_POST) === true) {
            //We save the tid in the both novalnet tables with a transaction
            $this->saveTid('Transfer');

            if ($this->status === 'success' || $this->status === 'user_cancel_transfer') {
                return \Yii::$app->response->redirect(['/shop/checkout', 'status' => $this->status, 'orderId' => $this->orderId]);
            } else {
                if (isset($_POST['status_text'])) {
                    $novalneterror = utf8_encode($_POST['status_text']);
                } else {
                    $novalneterror = Module::t('There was an error and your payment could not be completed. {status}', ['{status}' => '']);
                }
                \Yii::error($novalneterror);
                throw new Exception($novalneterror);
            }
        } else {
            \Yii::error('Paypal Error: Die Transaktion wurde nicht korrekt ausgeführt.');
            throw new Exception('Die Transaktion wurde nicht korrekt ausgeführt.');
        }
    }

    /**
     * This function send the transfer data to paypal.
     * Here we have a return url that handles the return values
     * @param object the order
     * @param array the form values from the paymentPane
     * @param string the redirect route where we should redirect after payment is complete.
     */
    public function execute($order, $model)
    {
        $data = [
            'USER' => $this->live ? self::API_USERNAME : self::API_USERNAME_SANDBOX,
            'PWD' => $this->live ? self::API_PASSWORD : self::API_PASSWORD_SANDBOX,
            'SIGNATURE' => $this->live ? self::API_SIGNATUR : self::API_SIGNATUR_SANDBOX,
            'METHOD' => 'SetExpressCheckout',
            'VERSION' => 109.0,
            'NOSHIPPING' => 1,
            'CANCELURL' => 'https://' . $_SERVER['HTTP_HOST'] . '/shop/paypal/basic-cancel',
            'RETURNURL' => 'https://' . $_SERVER['HTTP_HOST'] . '/shop/paypal/basic',
        ];

        //Set the Articles to the data array() to see the ordered Articles on paypal page;
        $total = 0;
        foreach ($order->orderArticles as $k => $Article) {
            $data["L_PAYMENTREQUEST_0_NUMBER0{$k}"] = $Article['id'];
            $data["L_PAYMENTREQUEST_0_NAME{$k}"] = $Article['title'];
            $data["L_PAYMENTREQUEST_0_QTY{$k}"] = $Article['qty'];
            $data["L_PAYMENTREQUEST_0_AMT{$k}"] = \number_format($Article['sell_price'], 2, '.', '');
            $total += \number_format($Article['total_price'], 2, '.', '');
        }

        // The total price of all Articles
        $data['PAYMENTREQUEST_0_CURRENCYCODE'] = 'EUR';
        $data['PAYMENTREQUEST_0_ITEMAMT'] = $total;
        $data['ALLOWNOTE'] = 0;
        // $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';
        $data['AMT'] = $total;
        $data['PAYMENTREQUEST_0_AMT'] = $total;
        
        
        // Set data for giropay
        $data['GIROPAYSUCCESSURL'] = 'https://' . $_SERVER['HTTP_HOST'] . '/shop/paypal/giropay-success';
        $data['GIROPAYCANCELURL'] = 'https://' . $_SERVER['HTTP_HOST'] . '/shop/paypal/giropay-cancel';
        $data['BANKTXNPENDINGURL'] = 'https://' . $_SERVER['HTTP_HOST'] . '/shop/paypal/giropay-pending'; // This url is used when the user cancel the giropay payment and switch to bank transfer method (disabled in my profile)
        
        //Set our custom data
        $data['PAYMENTREQUEST_0_INVNUM'] = $order->id;
        
        


        $query = http_build_query($data, '', '&');
        $url = $this->live ? self::ENDPOINT : self::ENDPOINT_SANDBOX;
        $response = $this->doPost($url, $query);
        parse_str($response, $parsed);

        if ($parsed['ACK'] === 'Success') {
            $redirectUrl = $this->live ? 'https://www.paypal.com/cgi-bin/webscr&cmd=_express-checkout&useraction=commit&token=' . $parsed['TOKEN'] : 
                                         'https://www.sandbox.paypal.com/cgi-bin/webscr&cmd=_express-checkout&useraction=commit&token=' . $parsed['TOKEN'];  
            \Yii::$app->response->redirect($redirectUrl)->send();
            exit();
        } else {
            //@todo
        }
    }

    /**
     * This function is called to get the express checkout details
     */
    public function getExpressCheckoutDetails()
    {
        //$this->orderId = $_POST['orders_id'];
        //$this->tid = $_POST['tid'];
        //$this->setPaymentStatus($_POST['status']);
        //$this->setOrderStatus();


        $data = [
            'USER' => $this->live ? self::API_USERNAME : self::API_USERNAME_SANDBOX,
            'PWD' => $this->live ? self::API_PASSWORD : self::API_PASSWORD_SANDBOX,
            'SIGNATURE' => $this->live ? self::API_SIGNATUR : self::API_SIGNATUR_SANDBOX,
            'METHOD' => 'GetExpressCheckoutDetails',
            'VERSION' => 109.0,
            'TOKEN' => $_GET['token'],
        ];

        $query = http_build_query($data, '', '&');
        $url = $this->live ? self::ENDPOINT : self::ENDPOINT_SANDBOX;
        $response = $this->doPost($url, $query);
        parse_str($response, $parsed);

        return $parsed;
    }

    /**
     * This function do the express checkout payment to complete the transaction
     */
    public function doExpressCheckoutPayment($details)
    {
        //$this->orderId = $_POST['orders_id'];
        //$this->tid = $_POST['tid'];
        //$this->setPaymentStatus($_POST['status']);
        //$this->setOrderStatus();


        $data = [
        'USER' => $this->live ? self::API_USERNAME : self::API_USERNAME_SANDBOX,
        'PWD' => $this->live ? self::API_PASSWORD : self::API_PASSWORD_SANDBOX,
        'SIGNATURE' => $this->live ? self::API_SIGNATUR : self::API_SIGNATUR_SANDBOX,
        'METHOD' => 'DoExpressCheckoutPayment',
        'VERSION' => 109.0,
        'TOKEN' => $_GET['token'],
        'PAYERID' => $details['PAYERID'],
        'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
        'PAYMENTREQUEST_0_AMT' => $details['AMT'],
        'PAYMENTREQUEST_0_CURRENCYCODE' => $details['CURRENCYCODE'],

        ];

        $query = http_build_query($data, '', '&');
        $url = $this->live ? self::ENDPOINT : self::ENDPOINT_SANDBOX;
        $response = $this->doPost($url, $query);
        parse_str($response, $parsed);
        
        return $parsed;
    }

}
