<?php

namespace app\modules\eshop\models;

use app\modules\eshop\Module;
use yii\base\Model;

/**
 * Address Class
 * Address is the data structure for keeping
 * address form data. It is used by the checkout index action of 'CheckoutController'.
 */
class Address extends Model
{
	public $firstname;
	public $lastname;
	public $street1;
	public $street2;
	public $postcode;
	public $city;
	public $country='DE';
	public $phone;
	
	

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return [
			[['firstname', 'lastname', 'street1', 'city', 'postcode', 'country'], 'required', 'message'=>'Ergänzen Sie bitte die fehlende Angabe für {attribute}'],
			
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
          'firstname'=>Module::t('Firstname'),
		  'lastname'=>Module::t('Lastname'),
		  'street1'=>Module::t('Street'),
		  'street2'=>Module::t('Street2'),
		  'city'=>Module::t('City'),
		  'postcode'=>Module::t('Postcode'),
		  'country'=>Module::t('Country'),
		  'phone'=>Module::t('Phone'),
		];
	}
}