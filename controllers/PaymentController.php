<?php

namespace kmergen\eshop\controllers;

use Yii;
use kmergen\eshop\models\Payment;
use kmergen\eshop\models\PaymentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
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
     * Render the paypal rest pane and returns it via ajax.
     * @return string
     */
    public function actionPaypalRestPane()
    {
       $form = new \yii\bootstrap4\ActiveForm();
       $form->enableClientScript = false;

       $data = [];
       $data['html'] = $this->renderAjax('@kmergen/eshop/paypal/views/rest_pane');
       $data['errorMessages'] = [];

       return $this->asJson($data);
    }

    /**
     * Render the stripe card pane and returns it via ajax.
     * @return string
     */
    public function actionStripeCardPane()
    {
        $form = new \yii\bootstrap4\ActiveForm();
        $form->enableClientScript = false;

        $data = [];
        $data['html'] = $this->renderAjax('@kmergen/eshop/stripe/views/card_pane', [
            'model' => new \kmergen\eshop\stripe\models\Card(),
            'form' => $form,
        ]);
        $data['errorMessages'] = [];
        return $this->asJson($data);
    }

    /**
     * Render the stripe sepa pane and returns it via ajax.
     * @return string
     */
    public function actionStripeSepaPane()
    {
       $form = new \yii\bootstrap4\ActiveForm();
       $form->enableClientScript = false;
       $form->fieldConfig =  function ($model, $attribute) {
        $data['template'] = "{beginWrapper}\n{label}\n{input}\n{endWrapper}{hint}\n{error}\n";
        $data['wrapperOptions'] = ['class' => empty(Html::getAttributeValue($model, $attribute)) ? 'input-group inplace-group' : 'input-group inplace-group has-value'];
        return $data;
    };
       $model = new \kmergen\eshop\stripe\models\Sepa();
       $data = [];
       $data['html'] = $this->renderAjax('@kmergen/eshop/stripe/views/sepa_pane', [
            'model' => $model,
            'form' => $form,
        ]);

        $data['errorMessages'] = [
            'bankaccountOwner' => [
                'required' => Yii::t('eshop', 'Please enter here the {0}', $model->getAttributeLabel('bankaccountOwner'))
            ],
            'email' => [
                'required' => Yii::t('eshop', 'Please enter here your email address'),
                'email' => Yii::t('eshop', 'The entered email address does not seem to be correct')
            ],
        ];

        return $this->asJson($data);
    }

    /**
     * Lists all Payment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Payment model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Payment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Payment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Payment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Payment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Payment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('eshop', 'The requested page does not exist.'));
    }
}
