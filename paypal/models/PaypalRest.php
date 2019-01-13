<?php

namespace kmergen\eshop\paypal\models;

use kmergen\eshop\Module;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Paypal Rest Api model.
 * The underlying model for the rest pane.
 */
class PaypalRest extends Model
{
  public $text;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return [
			// name, email, subject and body are required
			['text', 'safe'],
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
		    'text'=>'',
		];
	}
}
