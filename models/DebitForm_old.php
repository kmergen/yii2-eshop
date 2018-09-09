<?php

namespace app\modules\eshop\models;

use yii\base\Model;
use yii\helpers\Html;
use app\modules\eshop\Module;

/**
 * Debit Model
 * This model hold the data structure for keeping
 * debit form data. It is used by the 'index' action of 'CheckoutController'.
 */
class DebitForm extends Model
{

    public $account_no = '';
    public $bank_code = '';
    public $account_holder = '';
    public $iban;
    public $bic;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            // Required attributes
            [['account_no', 'bank_code', 'iban'], 'required'],
            // The Bank Code will be validate from the bank_code function
            ['account_no', 'validateAccount_no'],
            ['bank_code', 'validateBank_code'],
            ['iban', 'app\modules\eshop\components\IbanValidator'],
            ['bic', 'app\modules\eshop\components\BicValidator'],
            ['account_holder', 'safe'],
        );
    }

    /**
     * Validate the account no.
     */
    public function validateAccount_no()
    {
        if (strlen(preg_replace("/[^0-9]/", "", $this->account_no)) < 3) {
            $this->addError('account_no', Module::t('Invalid {attribute}', ['attribute' => $this->getAttributeLabel('account_no')]));
        }
    }

    /**
     * Validate the bank code.
     */
    public function validateBank_code()
    {
        if (strlen(preg_replace("/[^0-9]/", "", $this->bank_code)) < 8) {
            $this->addError('bank_code', Module::t('Invalid {attribute}', ['attribute' => $this->getAttributeLabel('bank_code')]));
        }
    }

 
    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'account_no' => Module::t('Account no'),
            'bank_code' => Module::t('Bank code'),
            'iban' => Module::t('IBAN'),
            'bic' => Module::t('BIC/SWIFT'),
            'account_holder' => Module::t('Account holder'),
        );
    }
}
