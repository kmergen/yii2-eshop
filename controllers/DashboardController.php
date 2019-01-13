<?php

namespace kmergen\eshop\controllers;

use yii\web\Controller;

class DashboardController extends AdminController
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
