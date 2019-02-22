<?php

namespace kmergen\eshop\models\braintree;

use yii\base\Model;

/**
 * Paypal Form Model
 */
class PaypalForm extends Model
{

    public $infoText = '';

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            ['infoText', 'safe'],
        );
    }


    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return [
            'infoText' => Module::t('Info text'),
        ];
    }
}
