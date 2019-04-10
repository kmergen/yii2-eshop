<?php

namespace kmergen\eshop\stripe\models;

use kmergen\eshop\Module;
use yii\base\Model;
use yii\helpers\Html;
use Yii;

/**
 * Stripe SEPA model.
 * The underlying model for the sepa pane.
 */
class Sepa extends Model
{
  public $paygate;
  public $bankaccountOwner;
  public $email;
  public $source;

    public function init()
    {
        $module = Module::getInstance();
        $this->paygate = Yii::createObject($module->paymentMethods['stripe_card']['paygate']);
    }


	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return [
			// name, email, subject and body are required
			[['bankaccountOwner', 'email', 'source'], 'required', 'message' => Yii::t('eshop', 'Please enter the {attribute} here ')],
			['email', 'email'],
            ['paygate', 'safe']
		];
	}

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return [
		    'bankaccountOwner'=>Yii::t('eshop', 'Bankaccount Owner'),
		  'email'=> Yii::t('eshop', 'Email'),
		];
	}

	public function createCharge($cart) {


	    // At the moment sepa debit is not unlocked.
        // From Stripe it is in planning to migrate sepa from Source Api to the paymentIntent Api.
        // We do the stuff if we are unlocked from Stripe for sepa payment. Then we can see if the migration is done or
        // if we go on with the Stripe Source Api.
	    return null;

//        \Stripe\Stripe::setApiKey($this->paygate->secretKey);
//
//        $charge = \Stripe\Charge::create([
//            'amount' => 1099,
//            'currency' => 'eur',
//            'customer' => 'cus_AFGbOSiITuJVDs',
//            'source' => $this->source,
//        ]);
    }
}
