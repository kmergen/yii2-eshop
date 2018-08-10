<?php

namespace app\modules\eshop\components\novalnet;

use app\modules\eshop\components\Paygate;
use yii\base\Exception;

/**
 * The Novalnet Paygate.
 * This hold the informations about the Novalnet Paygate
 */
class Novalnet extends Paygate
{

    const VENDOR_ID = 647;
    const TARIFF_ID = 1706;
    const PRODUCT_ID = 919;
    const AUTHCODE = 'l2cStrBFbGsKiG3SirDEg7VtipsM1o';

    public $id = 'Novalnet';
    public $testMode = 1;
    public $curlTimeout = 360;

    /**
     * Sends the payment data to Novalnet.
     * Response an error or that the payment is done with some data
     * @param string url the url to Novalnet paygate
     * @param array data
     */
    public function novalnetDoPost($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSe);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlTimeout);

        $response = curl_exec($ch);

        // Log any errors to the watchdog.
        if ($error = curl_error($ch)) {
            \Yii::$app->getLog()->log('cUrl error: ' . $error, Logger::LEVEL_ERROR);
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


