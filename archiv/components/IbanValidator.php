<?php

namespace app\modules\eshop\components;

use Yii;
use yii\validators\Validator;

class IbanValidator extends Validator
{

    /**
     * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
     */
    public $allowedContries = [
        
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('app', '{attribute} is not a valid IBAN No.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        // An IBAN without a country code is not an IBAN.
        if (0 === preg_match('/[A-Za-z]/', $value)) {
            $this->addError($object, $attribute, $this->message, []);
            return;
        }

        $teststring = preg_replace('/\s+/', '', $value);

        if (strlen($teststring) < 4) {
            $this->addError($object, $attribute, $this->message, []);
            return;
        }

        $teststring = substr($teststring, 4)
            . strval(ord($teststring{0}) - 55)
            . strval(ord($teststring{1}) - 55)
            . substr($teststring, 2, 2);

        $teststring = preg_replace_callback('/[A-Za-z]/', function ($letter) {
            return intval(ord(strtolower($letter[0])) - 87);
        }, $teststring);

        $rest = 0;
        $strlen = strlen($teststring);
        for ($pos = 0; $pos < $strlen; $pos += 7) {
            $part = strval($rest) . substr($teststring, $pos, 7);
            $rest = intval($part) % 97;
        }

        if ($rest != 1) {
            $this->addError($object, $attribute, $this->message, []);
            return;
        }
    }

}
