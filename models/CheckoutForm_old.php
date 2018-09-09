<?php

namespace app\modules\eshop\models;

use app\modules\eshop\Module;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Checkout class.
 * Checkout is the data structure for keeping
 * checkout form data. It is used by the index action of 'CheckoutController'.
 */
class CheckoutForm extends Model
{
  public $paymentMethod;
  public $acceptAgb;
 		
	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return [
			// name, email, subject and body are required
			['paymentMethod', 'required', 'message'=>'Wählen Sie bitte eine Zahlungsweise aus.'],
			['acceptAgb', 'compare', 'compareValue'=>1, 'message'=>'Sie müssen unseren AGBs zustimmen.'],
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
			'paymentMethod'=>Module::t('Payment Method'),
		  'acceptAgb'=>'Ich akzeptiere die '. Html::a('Allgemeinen Geshäftsbedingungen', ['/site/agb'], ['target'=>'_blank']) .' von hundekauf.de',
		];
	}
}