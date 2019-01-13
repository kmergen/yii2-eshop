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
  public $bankaccountOwner;
  public $email;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return [
			// name, email, subject and body are required
			[['bankaccountOwner', 'email'], 'required'],
			['email', 'email'],
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
}
