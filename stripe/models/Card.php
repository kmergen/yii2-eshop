<?php

namespace kmergen\eshop\stripe\models;

use kmergen\eshop\Module;
use yii\base\Model;
use yii\helpers\Html;
use Yii;

/**
 * Stripe Card model.
 * The underlying model for the card pane.
 */
class Card extends Model
{
    public $cardHolderName;
    public $clientSecret;

    public function init()
    {
        $module = Yii::$app->getModule('eshop');
        $paygate = Yii::createObject(
            $module->paymentMethods['stripe_card']['paygate']
        );
        $intent = $paygate->getIntent();
        $this->clientSecret = $intent->client_secret;
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            ['cardHolderName', 'required'],
            [['cardHolderName', 'clientSecret'], 'string'],
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
            'cardHolderName' => Yii::t('eshop', 'Card Holder Name')
        ];
    }

}
