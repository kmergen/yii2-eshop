<?php

namespace app\modules\eshop\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;

class CartController extends Controller
{
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'emptycart'],
                        'allow' => true,
                        'roles' => ['*']
                    ],
                ],
            ],
        ];
    }
  

    /**
     * Render the cart
     */
    public function actionIndex()
    {
        $destination = '';

        $this->render('index', [
            'destination' => $destination,
        ]);
    }

    /**
     * Render the view for an empty cart
     */
    public function actionEmptyCart()
    {
        $destination = '';

        $this->render('emptycart', [
            'destination' => $destination,
        ]);
    }

}
