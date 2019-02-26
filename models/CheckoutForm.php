<?php

namespace kmergen\eshop\models;

use Yii;
use yii\base\Model;

/**
 * Ad checkout form model.
 */
class CheckoutForm extends Model
{
  public $paymentMethod;

  public $checkoutCanceled;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return [
			['paymentMethod', 'required', 'message'=> Yii::t('app', 'Please choose a payment method')],
            ['checkoutCanceled', 'safe']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'paymentMethod'=>Yii::t('app', 'Payment Method'),
		];
	}
}
