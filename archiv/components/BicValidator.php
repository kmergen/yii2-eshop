<?php

namespace app\modules\eshop\components;

use Yii;
use yii\validators\Validator;

class BicValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('app', '{attribute} is not a valid.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        // An IBAN without a country code is not an IBAN.
        if (0 === preg_match('([a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?)', $value)) {
            $this->addError($object, $attribute, $this->message, []);
            return;
        }
    }

}
