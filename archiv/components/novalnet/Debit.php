<?php

namespace app\modules\eshop\components\novalnet;

use app\modules\eshop\components\novalnet\Novalnet;

/**
 * Novalnet Debit
 * Extents the Novalnet paygate class with the debit paymentMethod
 */
class Debit extends Novalnet
{
    /**
     * Handle the Novalnet Direct Debit German payment method.
     * @param object the order
     * @param array the form values from the paymentPane
     * @param string the redirect route where we should redirect after payment is complete.
     */
    public function execute($order, $model)
    {
        $novalnetData = [
            'vendor' => self::VENDOR_ID,
            'auth_code' => self::AUTHCODE,
            'product' => self::PRODUCT_ID,
            'tariff' => self::TARIFF_ID,
            'amount' => $order->total * 100,
            'currency' => $this->currency,
            'key' => 2,
            'remote_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'first_name' => $order->billing_firstname,
            'last_name' => $order->billing_lastname,
            'street' => $order->billing_street1,
            'house_no' => '1',
            'zip' => $order->billing_postcode,
            'city' => $order->billing_city,
            'country_code' => $order->billing_country,
            'email' => \Yii::$app->user->getEmail($order->uid),
            'bank_code' => $model->bank_code,
            'bank_account' => $model->account_no,
            'bank_account_holder' => $model->account_holder,
            'acdc' => isset($_SESSION['acdc']) ? $_SESSION['acdc'] : 0,
            'test_mode' => $this->testMode,
        ];

        $query = http_build_query($novalnetData, '', '&');
        $response = $this->novalnetDopost('https://payport.novalnet.de/paygate.jsp', $query);
        parse_str($response, $parsed);
               
        $this->setPaymentStatus($parsed['status']);
        $this->orderId = $order->id;
        $this->setOrderStatus();
        $this->tid = $parsed['tid'];
        $this->saveTid('Debit');
    }
}
