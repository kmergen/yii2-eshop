<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use kmergen\eshop\models\ArticleCategory;
use kmergen\media\widgets\dropzone\Dropzone;

/* @var $this yii\web\View */
/* @var $model kmergen\eshop\models\Article */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal'
    ]); ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'category_id')->dropDownList(ArrayHelper::map(ArticleCategory::find()->asArray()->all(), 'id', 'name')) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'sell_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'default_qty')->dropDownList(array_combine(range(1, 20), range(1, 20))) ?>

    <?= $form->field($model, 'active')->dropDownList(['0' => Yii::t('eshop', 'No'), '1' => Yii::t('eshop', 'Yes')]) ?>

    <div class="row">
        <label class="col-md-2 col-form-label">
            Bilder
        </label>
        <div class="col-md-10">
            <?= Dropzone::widget([
                'model' => $model,
                'thumbStyle' => 'medium',
                'pluginOptions' => [
                    'url' => '/media/dropzone/upload',
                    'acceptedFiles' => '.png,.jpg,.gif,.jpeg',
                    'resizeWidth' => 1600,
                    'createImageThumbnails' => true,
                    'autoProcessQueue' => true,
                    'maxFiles' => 10,
                    'dictCancelUpload' => '',
                    'dictDefaultMessage' => '',
                    'params' => [
                        'targetUrl' => 'images/eshop/product'
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-10 offset-md-2">
            <?= Html::submitButton(Yii::t('eshop', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
