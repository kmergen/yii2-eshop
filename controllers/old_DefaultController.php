<?php

namespace app\modules\eshop\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
	/**
	 * At the moment only a message it can be in the future a catalog or something else
	 */
	public function actionIndex()
	{
        return $this->render('index', [
		  
		]);
	}
}