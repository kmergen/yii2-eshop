<?php

namespace app\modules\eshop\models;

use app\modules\eshop\Module;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Novalnet Transfer Model (Sofortüberweisung)
 * This model hold the data structure for keeping
 * transfer form data. It is used by the 'index' action of 'CheckoutController'.
 */
class TransferForm extends Model
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
