<?php

namespace kmergen\eshop\stripe\models;

use kmergen\eshop\Module;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Stripe Card model.
 * The underlying model for the card pane.
 */
class Card extends Model
{

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return [

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

		];
	}
}
