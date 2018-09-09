<?php

namespace kmergen\eshop\controllers;

use Yii;
use kmergen\eshop\models\OrderItem;
use kmergen\eshop\models\OrderItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OrderItemController implements the CRUD actions for OrderItem model.
 */
class OrderItemController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all OrderItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single OrderItem model.
     * @param integer $order_id
     * @param integer $article_id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($order_id, $article_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($order_id, $article_id),
        ]);
    }

    /**
     * Creates a new OrderItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new OrderItem();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'order_id' => $model->order_id, 'article_id' => $model->article_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing OrderItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $order_id
     * @param integer $article_id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($order_id, $article_id)
    {
        $model = $this->findModel($order_id, $article_id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'order_id' => $model->order_id, 'article_id' => $model->article_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing OrderItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $order_id
     * @param integer $article_id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($order_id, $article_id)
    {
        $this->findModel($order_id, $article_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the OrderItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $order_id
     * @param integer $article_id
     * @return OrderItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($order_id, $article_id)
    {
        if (($model = OrderItem::findOne(['order_id' => $order_id, 'article_id' => $article_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('eshop', 'The requested page does not exist.'));
    }
}
