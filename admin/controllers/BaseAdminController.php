<?php

namespace kmergen\eshop\admin\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * BaseAdminController is the base controller for all backend controllers.
 * It provides the base access control filters and configurations.
 */
class BaseAdminController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];
    }

}
