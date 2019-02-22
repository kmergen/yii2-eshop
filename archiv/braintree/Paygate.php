<?php
/**
 * KM Websolutions Projects
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2010 KM Websolutions
 * @license http://www.yiiframework.com/license/
 */

namespace kmergen\eshop\braintree;

use yii\base\BaseObject;
use yii\base\Exception;
use Braintree\Configuration;
use Braintree\Gateway;

/**
 * The Braintree paygate.
 */
class Paygate extends BaseObject
{
    /**
     * @var string $environment
     */
    public $environment;

    /**
     * @var string $merchantId .
     */
    public $merchantId;

    /**
     * @var string $publicKey .
     */
    public $publicKey;

    /**
     * @var string $privateKey .
     */
    public $privateKey;

    /**
     * @var object $_config .
     */
    private $_config;

    /**
     * @var object $_gateway .
     */
    private $_gateway;


    public function init() {
        $this->_config = new Configuration([
            'environment' => $this->environment,
            'merchantId' => $this->merchantId,
            'publicKey' => $this->publicKey,
            'privateKey' => $this->privateKey,
        ]);

        $this->_gateway = new Gateway($this->_config);
    }

    public function getGateway() {
        return $this->_gateway;
    }


    public function execute($order, $customer, $params = [])
    {
        if (!isset($params['nonce'])) {
            throw new Exception('Nonce must be set.');
        }

        $result = $this->_gateway->transaction()->sale([
            'amount' => $order->total,
            'paymentMethodNonce' => $params['nonce'],
            'orderId' => $order->id,
            'options' => [
                'submitForSettlement' => true,
                'paypal' => [
                    'customField' => "PayPal custom field",
                    'description' => "Description for PayPal email receipt",
                ],
            ],
        ]);
        if ($result->success) {
            $msg ="Success ID: " . $result->transaction->id;
        } else {
            $msg = '';
            foreach($result->errors->deepAll() as $error) {
                $msg .= 'Error: ' . $error->code . ": " . $error->message . "\n";
            }
        }
    }


}

