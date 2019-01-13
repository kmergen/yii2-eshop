<?php

namespace kmergen\eshop\components\Paypal;

use kmergen\eshop\components\Paygate;
use yii\base\Exception;

/**
 * The Paypal Paygate.
 * This hold the basic informations about the Paypal Paygate
 */
class Paypal extends Paygate
{

    const API_SIGNATUR = 'A5R3Z2vEqlw8r3dVdjYL7HAHicUyA.SA7IvRkmcjy7iNsx1ViMC2wn6w';
    const API_USERNAME = 'klaus.mergen_api1.web.de';
    const API_PASSWORD = 'DC5LQAEVTA6EBATN';
    const ENDPOINT = 'https://api-3t.paypal.com/nvp';

    const API_SIGNATUR_SANDBOX = 'AFcWxV21C7fd0v3bYYYRCpSSRl31AInuqmY-NrGhzuWh5EgtZdHPUzTe';
    const API_USERNAME_SANDBOX = 'kmergen-test_api1.web.de';
    const API_PASSWORD_SANDBOX = '1388849436';
    const ENDPOINT_SANDBOX = 'https://api-3t.sandbox.paypal.com/nvp';



    public $id = 'Paypal';
    public $curlTimeout = 360;
    public $live = false; // Switch between live(true) and sandbox(false) mode.

    /**
     * Sends the payment data to Payment.
     * Response an error or that the payment is done with some data
     * @param string url the url to Novalnet paygate
     * @param array data
     */
    public function doPost($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlTimeout);

        $response = curl_exec($ch);

        // Log any errors to the watchdog.
        if ($error = curl_error($ch)) {
            \Yii::error('cUrl error: ' . $error);
            throw new Exception('Es ist ein Systemfehler aufgetreten');
        }
        curl_close($ch);
        return $response;
    }

    public function setPaymentStatus($status)
    {
        if ($status == 100) {
            $this->status = 'success';
        } elseif ($status == 94) {
            $this->status = 'user_cancel_transfer';
        } else {
            $this->status = 'error';
        }
    }

}


