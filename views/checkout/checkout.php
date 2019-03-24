<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use kmergen\eshop\helpers\Cart;
use yii\helpers\ArrayHelper;
use kmergen\widgets\CharCounter;
use tigrov\intldata\Country;
use yii\helpers\Url;
use dosamigos\typeahead\Bloodhound;
use dosamigos\typeahead\TypeAhead;
use kmergen\eshop\CheckoutAsset;

/**
 * @var yii\web\View $this
 * @var common\models\Ad $model
 * @var yii\bootstrap\ActiveForm $form
 */

$this->title = Yii::t('app/eshop', 'Checkout');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('https://js.stripe.com/v3/');
CheckoutAsset::register($this);
?>

<div class="justify-content-center">

    <?php
    $form = ActiveForm::begin([
        'id' => 'checkoutForm',
        'enableClientValidation' => true,
        'enableAjaxValidation' => false,
        'validateOnBlur' => false,
        'validateOnSubmit' => true,
        'validationStateOn' => ActiveForm::VALIDATION_STATE_ON_CONTAINER,
        'options' => ['autocomplete' => 'off'],
        'fieldConfig' => function ($model, $attribute) {
            $data['template'] = "{beginWrapper}\n{label}\n{input}\n{endWrapper}{hint}\n{error}\n";
            $data['wrapperOptions'] = ['class' => empty(Html::getAttributeValue($model, $attribute)) ? 'input-group inplace-group' : 'input-group inplace-group has-value'];
            return $data;
        }
    ]);

    ?>
<!--Payment Content-->
    <h4 class="form-section-heading"><?= Yii::t('app', 'view.ad-checkout.title') ?></h4>
    <div class="row">
        <div class="col-md-6">
            <!--    Begin payment wall-->
            <div class="payment-wall" id="paymentWall">

                <?php foreach ($module->paymentMethods as $k => $pm): ?>
                    <?php
                    $attributes = [
                        'id' => 'collapse-' . $k,
                        'class' => $model->paymentMethod === $k ? 'collapse show' : 'collapse',
                        'aria-labelledby' => 'heading-' . $k,
                        'data-parent' => '#paymentWall',
                        'data-paymentmethod' => $k,
                        'data-paneurl' => $pm['paneurl'],
                    ]
                    ?>
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button id="btn-<?= $k ?>" class="btn btn-link" type="button" data-toggle="collapse"
                                        data-target="#collapse-<?= $k ?>"
                                        aria-expanded="true" aria-controls="collapse-<?= $k ?>">
                                    <?= $pm['label'] ?>
                                </button>
                            </h5>
                        </div>

                        <div<?= Html::renderTagAttributes($attributes) ?>>
                            <div class="text-center">
                                <div class="spinner-border mt-5" role="state">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <div class="card-body"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?= $form->field($model, 'paymentMethod', ['template' => "{input}\n{error}\n"])->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'checkoutCanceled', ['template' => "{input}"])->hiddenInput()->label(false) ?>
            <!--    End payment wall-->
        </div>

<!--Cart Content-->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col"><?= Yii::t('app', 'Product') ?></th>
                                <th scope="col"><?= Yii::t('app', 'Price') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?= Yii::t('app', 'Basic Ad') ?></td>
                                <td><?= Yii::$app->getFormatter()->asCurrency(0) ?></td>
                            </tr>
                            <?php foreach ($cart->items as $item) : ?>
                                <tr>
                                    <td><?= $item->title ?></td>
                                    <td><?= Yii::$app->getFormatter()->asCurrency($item->sell_price) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td><strong><?= Yii::t('app', 'Total price') ?></strong></td>
                                <td><strong><?= Yii::$app->getFormatter()->asCurrency($cart->total) ?></strong>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!--Address Content-->
    <div class="row">
        <div class="col-md-6">

        </div>
        <div class="col-md-6">
            <?php if ($address !== null): ?>
                <?= $form->field($address, 'fullname')->textInput(['maxlength' => true]) ?>
                <?= $form->field($address, 'street')->textInput(['maxlength' => true]) ?>
                <?= $form->field($address, 'postcode')->textInput(['maxlength' => true]) ?>
                <?= $form->field($address, 'city')->textInput(['maxlength' => true]) ?>
            <?php endif; ?>
        </div>
    </div>


<!--Buttons-->
    <div class="row mt-3 mb-2">
        <div class="col-6">
            <div class="form-group form-actions">
                <button id="btnCancel" class="btn btn-link" type="reset" name="btnCancel">
                    <span class="small"><< <?= Yii::t('app', 'view.ad-checkout.cancelButtonText') ?></span>
                </button>
            </div>
        </div>
        <div class="col-6 text-right">
            <button id="btnPay" class="btn btn-success" type="submit" name="btnPay">
                <span><?= Yii::t('app', 'view.ad-checkout.payButtonText') ?> >></span>
            </button>
        </div>
    </div>
</div>

<p class="small">
    <?= Yii::t('app', 'view.ad-checkout.acceptText', [
        Yii::t('app', 'view.ad-checkout.payButtonText'),
        Html::a(Yii::t('app', 'AGB'), ['/site/agb'], ['target' => 'blank']),
        Html::a(Yii::t('app', 'Widerruf'), ['/site/widerruf'], ['target' => 'blank']),
        Html::a(Yii::t('app', 'Datenschutzhinweise'), ['/site/datenschutz'], ['target' => 'blank']),
        Yii::$app->name
    ]) ?>
</p>


<?php ActiveForm::end(); ?>
</div>

<?php
$stripeId = $module->paymentMethods['stripe_card']['paygate']['publishKey'];
$this->registerJs("KMeshop.checkout.init({stripeId: '$stripeId'});");
?>







