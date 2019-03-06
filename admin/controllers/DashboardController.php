<?php

namespace kmergen\eshop\admin\controllers;

use yii\data\ActiveDataProvider;
use Da\User\Model\User;

/**
 * Default controller for the [[backend]] module
 *
 * @author Klaus Mergen <klausmergen66@gmail.com>
 */
class DashboardController extends BaseAdminController
{

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'cron' => 'app\components\Cron',
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'layout' => 'blank'
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'dataProviderRecentUsers' => $this->getDataProviderRecentUsers(),
        ]);
    }


    /**
     * Lists the recent User models.
     * @return mixed
     */
    public function actionRecentUsers()
    {
        return $this->renderAjax('_recent-users', [
            'dataProvider' => $this->getDataProviderRecentUsers()
        ]);
    }

    /**
     * Returns a data provider
     * @return \yii\data\ActiveDataProvider
     */
    protected function getDataProviderRecentUsers()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->orderBy('created_at desc'),
            'pagination' => [
                'pageSize' => 5,
            ],
            'sort' => false
        ]);
        return $dataProvider;
    }

}
