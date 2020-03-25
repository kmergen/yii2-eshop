<?php

use yii\bootstrap4\Html;
use kmergen\widgets\ActiveForm;
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
 * @var kmergen\eshop\models\Cart $cart
 */

$this->title = Yii::t('app/eshop', 'Checkout');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('https://js.stripe.com/v3/');
$assets = CheckoutAsset::register($this);
?>

    <div class="justify-content-center">

        <?php
        $form = ActiveForm::begin([
            'id' => 'checkoutForm',
            'enableClientValidation' => true,
            'enableAjaxValidation' => false,
            'validateOnBlur' => false,
            'validateOnSubmit' => true,
            'options' => ['autocomplete' => 'off'],
            'enableFloatLabels' => true,
            'floatLabelOptions' => ['style' => 2]
        ]);

        ?>
        <!--Payment Content-->
        <h4 class="form-section-heading"><?= Yii::t('eshop', 'view.ad-checkout.title') ?></h4>
        <div class="row">
            <div class="col-md-6">
                <!--    Begin payment wall-->
                <div class="payment-wall" id="paymentWall">
                    <?php foreach ($module->paymentMethods as $k => $pm): ?>
                    <?php if ($pm['enabled']) : ?>
                    <?php
                    $attributes = [
                        'id' => 'collapse-' . $k,
                        'class' => $model->paymentMethod === $k ? 'collapse show' : 'collapse',
                        'aria-labelledby' => 'heading-' . $k,
                        'data-parent' => '#paymentWall',
                        'data-paymentmethod' => $k,
                        'data-paneurl' => Url::to($pm['paneurl']),
                    ]
                    ?>
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <div class="custom-control custom-radio" data-toggle="collapse"
                                 data-target="#collapse-<?= $k ?>"
                                 aria-expanded="true" aria-controls="collapse-<?= $k ?>">
                                <input type="radio" name="radio-pm" id="<?= $k ?>" class="custom-control-input">

                                <label class="custom-control-label"
                                       for="<?= $k ?>"><?= empty($pm['labelAsset']) ? $pm['labelText'] : $pm['labelText'] . ' ' . Html::img($assets->baseUrl . '/' . $pm['labelAsset'], ['class' => 'img-fluid']) ?></label>
                            </div>
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
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?= $form->field($model, 'paymentMethod', ['template' => "{input}\n{error}\n"])->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'checkoutCanceled', ['template' => "{input}"])->hiddenInput()->label(false) ?>
            <!--    End payment wall-->
        </div>

        <!--Cart Content-->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><?= Yii::t('eshop', 'Order Review') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($cart->items as $item) : ?>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <?= $item->title ?>
                            </div>
                            <div class="col-6">
                                <?= Yii::$app->getFormatter()->asCurrency($item->sell_price) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="row no-gutters mt-2">
                        <div class="col-6 font-weight-bold">
                            <?= Yii::t('eshop', 'Total price') ?>
                        </div>
                        <div class="col-6 font-weight-bold">
                            <?= Yii::$app->getFormatter()->asCurrency($cart->total) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--Address Content-->
    <!--    <div class="row">-->
    <!--        <div class="col-md-6">-->
    <!---->
    <!--        </div>-->
    <!--        <div class="col-md-6">-->
    <!--            --><?php //if ($address !== null): ?>
    <!--                --><?php // echo $form->field($address, 'fullname')->textInput(['maxlength' => true]) ?>
    <!--                --><?php // echo $form->field($address, 'street')->textInput(['maxlength' => true]) ?>
    <!--                --><?php // echo $form->field($address, 'postcode')->textInput(['maxlength' => true]) ?>
    <!--                --><?php // echo $form->field($address, 'city')->textInput(['maxlength' => true]) ?>
    <!--            --><?php //endif; ?>
    <!--        </div>-->
    <!--    </div>-->


    <!--Buttons-->
    <div class="row mt-3 mb-2">
        <div class="col-6">
            <div class="form-group form-actions">
                <button id="btnCancel" class="btn btn-link" type="reset" name="btnCancel">
                    <span class="small"><< <?= Yii::t('eshop', 'view.checkout.cancelButtonText') ?></span>
                </button>
            </div>
        </div>
        <div class="col-6 text-right">
            <button id="btnPay" class="btn btn-success" type="submit" name="btnPay">
                <span><?= Yii::t('eshop', 'view.checkout.payButtonText', ['price' => Yii::$app->getFormatter()->asCurrency($cart->total)]) ?> >></span>
            </button>
        </div>
    </div>


    <p class="small">
        <?= Yii::t('eshop', 'view.checkout.acceptText', [
            Yii::t('eshop', 'view.checkout.payButtonText', ['price' => Yii::$app->getFormatter()->asCurrency($cart->total)]),
            Html::a(Yii::t('eshop', 'Terms of Service'), $module->termsOfServiceUrl, ['target' => 'blank']),
            Html::a(Yii::t('eshop', 'Revocation'), $module->revocationUrl, ['target' => 'blank']),
            Html::a(Yii::t('eshop', 'Data protection'), $module->dataProtectionUrl, ['target' => 'blank']),
            Yii::$app->name
        ]) ?>
    </p>


<?php ActiveForm::end(); ?>
    </div>
<?php
$stripeId = $module->paymentMethods['stripe_card']['paygate']['publishKey'];
$this->registerJsFile('https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js');
$this->registerJs("KMeshop.checkout.init({stripeId: '$stripeId', floatlabels: floatlabels});");
