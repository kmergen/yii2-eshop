<?php

namespace app\modules\eshop\controllers;

use app\modules\eshop\components\novalnet\Transfer;
use app\modules\eshop\controllers\CheckoutController;
use yii\filters\VerbFilter;

/**
 * This controller handles the responses from Novalnet
 */
class NovalnetController extends CheckoutController
{

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'instantbank' => ['post'],
                ],
            ],
        ];
    }

    public function actionInstantBank()
    {
        $transfer = new Transfer;
        $transfer->transferComplete();
    }

}
