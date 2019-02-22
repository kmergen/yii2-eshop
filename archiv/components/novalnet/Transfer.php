<?php

namespace app\modules\eshop\components\novalnet;

use app\modules\eshop\Module;
use app\modules\eshop\components\novalnet\Novalnet;
use yii\base\Exception;


/**
 * Novalnet Transfer Model (Sofortüberweisung)
 * This model hold the data structure for keeping
 * transfer form data. It is used by the 'index' action of 'CheckoutController'.
 */
class Transfer extends Novalnet
{

    const PAYMENT_ACCESS_ID = '303ed4c69846ab36c2904d3ba8573050';

    /**
     * This function is called from NovalnetController when the transfer is complete
     */
    public function transferComplete()
    {
        $this->orderId = $_POST['orders_id'];
        $this->tid = $_POST['tid'];
        $this->setPaymentStatus($_POST['status']);
        $this->setOrderStatus();

        if ($this->checkHash($_POST) === true) {
            //We save the tid in the both novalnet tables with a transaction
            $this->saveTid('Transfer');

            if ($this->status === 'success' || $this->status === 'user_cancel_transfer')  {
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
            \Yii::error('Sofortueberweisung konnte nicht korrekt ausgefuehrt werden.');
            throw new Exception('Die Transaktion wurde nicht korrekt ausgeführt.');
        }
    }

    /**
     * This function send the transfer data to novalnet.
     * Here we have a return url that handles the return values
     * @param object the order
     * @param array the form values from the paymentPane
     * @param string the redirect route where we should redirect after payment is complete.
     */
    public function execute($order, $model)
    {
        $uniqid = $order->id . time();
        $amount = $order->total * 100;

        $data = [
            'utf8' => 1,
            'vendor' => self::VENDOR_ID,
            'product' => $this->encode(self::PRODUCT_ID),
            'key' => 33,
            'tariff' => $this->encode(self::TARIFF_ID),
            'auth_code' => $this->encode(self::AUTHCODE),
            'currency' => $this->currency,
            'amount' => $this->encode($amount),
            'first_name' => $order->billing_firstname,
            'last_name' => $order->billing_lastname,
            'email' => \Yii::$app->user->getEmail($order->uid),
            'street' => $order->billing_street1,
            'search_in_street' => 1,
            'city' => $order->billing_city,
            'zip' => $order->billing_postcode,
            'country_code' => $order->billing_country,
            'remote_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'lang' => 'DE',
            'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/shop/novalnet/instant-bank',
            'return_method' => 'POST',
            'error_return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/shop/novalnet/instant-bank',
            'error_return_method' => 'POST',
            'test_mode' => $this->encode($this->testMode),
            'live_mode' => 0, //Nur zum Testen, nach dem Test Zeile auskommentieren oder löschen
            'user_variable_0' => $_SERVER['HTTP_HOST'],
            'uniqid' => $this->encode($uniqid),
            'orders_id' => $order->id,
        ];

        // Compute hash value.
        $hashP = [
            'product_id' => $data['product'],
            'tariff' => $data['tariff'],
            'auth_code' => $data['auth_code'],
            'amount' => $data['amount'],
            'test_mode' => $data['test_mode'],
            'uniqid' => $data['uniqid'],
        ];
        $data['hash'] = $this->hashIt($hashP);

        $query = http_build_query($data, '', '&');
        $response = $this->novalnetDoPost('https://payport.novalnet.de/online_transfer_payport', $query);

        // The response is a form, which will itself handle the redirection to sofortueberweisung.de.
        print_r($response);
        exit;
    }

    /**
     * @file
     * Encoding and decoding of data to be transferred to Novalnet.
     * Based on sample code by Novalnet AG.
     */
    protected function encode($data)
    {
        $key = self::PAYMENT_ACCESS_ID;
        $data = trim($data);
        if ($data == '') {
            return 'Error: no data';
        }
        if (!function_exists('base64_encode') or !function_exists('pack') or !function_exists('crc32')) {
            return 'Error: func n/a';
        }

        try {
            $crc = sprintf('%u', crc32($data));# %u ist obligatorisch f�r ccrc32, gibt einen vorzeichenbehafteten Wert zur�ck
            $data = $crc . "|" . $data;
            $data = bin2hex($data . $key);
            $data = strrev(base64_encode($data));
        } catch (Exception $e) {
            echo('Error: ' . $e);
        }
        return $data;
    }

    protected function decode($data)
    {
        $key = self::PAYMENT_ACCESS_ID;
        $data = trim($data);
        if ($data == '') {
            return 'Error: no data';
        }
        if (!function_exists('base64_decode') or !function_exists('pack') or !function_exists('crc32')) {
            return 'Error: func n/a';
        }

        try {
            $data = base64_decode(strrev($data));
            $data = pack("H" . strlen($data), $data);
            $data = substr($data, 0, stripos($data, $key));
            $pos = strpos($data, "|");
            if ($pos === FALSE) {
                return("Error: CKSum not found!");
            }
            $crc = substr($data, 0, $pos);
            $value = trim(substr($data, $pos + 1));
            if ($crc != sprintf('%u', crc32($value))) {
                return("Error; CKSum invalid!");
            }
            return $value;
        } catch (Exception $e) {
            echo('Error: ' . $e);
        }
    }

    protected function hashIt($h)
    {
        $key = self::PAYMENT_ACCESS_ID;
        if (!$h)
            return 'Error: no data';
        if (!function_exists('md5')) {
            return 'Error: func n/a';
        }
        if (is_array($h)) {
            return md5($h['auth_code'] . $h['product_id'] . $h['tariff'] . $h['amount'] . $h['test_mode'] . $h['uniqid'] . strrev($key));
        } else {
            return md5($h . strrev($key));
        }
    }

    protected function checkHash($request)
    {
        $key = self::PAYMENT_ACCESS_ID;
        //$key = variable_get('sc_novalnet_transfer_key', 'a87ff679a2f3e71d9181a67b7542122c');
        if (!$request) {
            return FALSE;
        }
        if (is_array($request)) {
            $h['auth_code'] = $request['auth_code'];
            $h['product_id'] = $request['product'];
            $h['tariff'] = $request['tariff'];
            $h['amount'] = $request['amount'];
            $h['test_mode'] = $request['test_mode'];
            $h['uniqid'] = $request['uniqid'];
        }
        if ($request['hash2'] != $this->hashIt($h)) {
            return false;
        }
        return true;
    }

}
