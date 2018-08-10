<?php

namespace app\modules\eshop\models;

/**
 * This is the model class for table "eshop_order".
 *
 * @property string $id
 * @property string $uid
 * @property string $status
 * @property string $total
 * @property string $phone
 * @property string $billing_firstname
 * @property string $billing_lastname
 * @property string $billing_company
 * @property string $billing_street1
 * @property string $billing_street2
 * @property string $billing_zone
 * @property string $billing_postcode
 * @property string $billing_city
 * @property string $billing_country
 * @property string $data
 * @property string $created
 * @property string $modified
 * @property string $host
 * @property string $comment
 *
 * @property EshopOrderDetails $eshopOrderDetails
 */
class Order extends \yii\db\ActiveRecord
{

    public $orderArticles = [];
    private $_tableOrderDetails = 'eshop_order_details';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eshop_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'integer'],
            ['total', 'number'],
            [['data', 'comment'], 'string'],
            [['created', 'modified'], 'safe'],
            ['status', 'string', 'max' => 32],
            [['phone', 'billing_firstname', 'billing_lastname', 'billing_company', 'billing_street1', 'billing_street2', 'billing_zone', 'billing_postcode', 'billing_city', 'host'], 'string', 'max' => 255],
            [['billing_country'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'status' => 'Order Status',
            'total' => 'Total',
            'phone' => 'Phone',
            'billing_firstname' => 'Billing Firstname',
            'billing_lastname' => 'Billing Lastname',
            'billing_company' => 'Billing Company',
            'billing_street1' => 'Billing Street1',
            'billing_street2' => 'Billing Street2',
            'billing_zone' => 'Billing Zone',
            'billing_postcode' => 'Billing Postcode',
            'billing_city' => 'Billing City',
            'billing_country' => 'Billing Country',
            'data' => 'Data',
            'created' => 'Created',
            'modified' => 'Modified',
            'host' => 'Host',
            'comment' => 'Comment',
        ];
    }

    /**
     * @return \yii\db\ActiveRelation
     */
    public function getEshopOrderDetails()
    {
        return $this->hasOne(EshopOrderDetails::className(), ['order_id' => 'id']);
    }

    public function afterSave($insert)
    {
        if ($insert) {
            $command = \Yii::$app->db->createCommand();
            foreach ($this->orderArticles as $Article) {
                if ($Article['qty'] > 0) {
                    $record = [];
                    $record['order_id'] = $this->id;
                    $record['Article_id'] = $Article['id'];
                    $record['title'] = $Article['title'];
                    $record['sku'] = $Article['sku'];
                    $record['qty'] = $Article['qty'];
                    $record['sell_price'] = $Article['sell_price'];
                    $command->insert($this->_tableOrderDetails, $record)->execute();
                }
            }
        }

        parent::afterSave($insert);
        //$this->destroySessionVars();
    }

}
