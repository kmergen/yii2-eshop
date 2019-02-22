<?php

use yii\helpers\Html;
use app\modules\eshop\Module;
use app\helpers\Geo;
use yii\widgets\ActiveForm;
use app\modules\eshop\assets\EshopAsset;

$this->title = 'Checkout';

EshopAsset::register($this);
?>

<?php
if (YII_DEBUG) {
    $testdaten_js = "
		$(document.createElement('a')).attr({href:'#', id:'a-testdaten', className:'a-testdaten'}).text('Testdaten einfügen').insertBefore('form');

		// Testdaten in das Anzeigeformular einfügen
		function addTestdaten() {
			$('#address-firstname').val('Peter');
			$('#address-lastname').val('Mustermann');
			$('#address-street1').val('Musterstrasse 4');
			$('#address-postcode').val('54470');
			$('#address-city').val('Bernkastel-Kues');
		}
		$('#a-testdaten').click(function() {
			addTestdaten();
		  return false;
		});
		//Ende Testdaten einfügen
    ";

    $this->registerJs($testdaten_js);
}
?>




<?php
$form = ActiveForm::begin([
        'enableClientValidation' => false,
        'options' => [
            'id' => 'checkout-form',
            'class' => 'well form-horizontal checkout-form',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-md-6\">{input}{error}</div>",
            'labelOptions' => ['class' => 'col-md-2 control-label'],
            'inputOptions' => ['class' => 'form-control input-sm'],
        ],
    ]);
?>

<div class="form-section">
    <h3><?php echo Module::t('Address');?></h3>
    <div id="address">
        <?=$form->field($address, 'firstname')->textInput(['maxlength' => 100])?>
        <?=$form->field($address, 'lastname')->textInput(['maxlength' => 100])?>
        <?=$form->field($address, 'street1')->textInput(['maxlength' => 100])?>
        <?=$form->field($address, 'postcode')->textInput(['maxlength' => 6])?>
        <?=$form->field($address, 'city')->textInput(['maxlength' => 6])?>
        <?=$form->field($address, 'country')->dropDownList(Geo::IsoCountryList())?>
    </div>
</div>

<div class="form-section payment-methods">
    <h3><?=$checkoutForm->getAttributeLabel('paymentMethod')?></h3> 
    <div class="form-group">
        <div class="col-md-2">
            <?=Html::activeRadioList($checkoutForm, 'paymentMethod', $this->context->module->listPaymentMethods(), ['itemOptions' => ['class' => 'payment-method']])?>
        </div>
        <div class="col-md-8">
            <div id="payment-method-pane" class="">
                <?php if ($paymentModel !== null):?>
                    <?=$this->render(strtolower($checkoutForm->paymentMethod) . '_pane', ['model' => $paymentModel])?>
                <?php endif;?>
            </div>
        </div>
    </div>
    <?=Html::error($checkoutForm, 'paymentMethod', ['class' => 'error-block payment-methods-error'])?>
</div>

<div class="form-section">
    <h3>Ausgewählte Artikel</h3>
    <div id="cart-pane" class="row">
        <div class="col-md-8">
            <?=$cartPane?>
        </div>
    </div>
</div>

<div class="form-section">
    <?=$form->field($checkoutForm, 'acceptAgb')->checkbox(['label' => $checkoutForm->getAttributeLabel('acceptAgb')])?>
</div>

<div class="buttons">
    <button class="btn btn-success" type="submit" name="btnSave">Absenden</button>
    <button class="btn btn-default btn-xs" type="submit" name="btnAbort">Abbrechen</button>
</div>

<?php ActiveForm::end();?>




