<?php

namespace app\modules\eshop\components;

use yii\base\Component;
use app\modules\eshop\models\Order;

/**
 * The Novalnet Paygate.
 * This hold the informations about the Novalnet Paygate
 */
class Paygate extends Component
{

    /**
     * string The paygate id.
     */
    public $id;

    /**
     * integer The transaction id.
     */
    public $tid;

    /**
     * integer The order id who this payment transaction belongs to.
     */
    public $orderId;

    /**
     * string The currency for this payment transaction.
     */
    public $currency = 'EUR';

    /**
     * string The payment status.
     */
    public $status;

    public function saveTid($payment_method)
    {
        //We save the tid 

        $affected = \Yii::$app->db->createCommand()->insert('eshop_payment_status', [
                'tid' => $this->tid,
                'order_id' => $this->orderId,
                'paygate' => $this->id,
                'status' => $this->status,
                'payment_method' => $payment_method,
            ])->execute();

        return $affected ? true : false;
    }

    /**
     * This function is called after the payment is done.
     */
    protected function setOrderStatus()
    {
        $order = Order::findOne($this->orderId);
        if ($this->status === 'success') {
            $order->status = 'complete';
        } else {
            $order->status = 'payment_error';
        }

        $order->save(false, ['status']);
    }

}
