<?php

use yii\helpers\Html;
?>

<div class="form-group">
    <div class="col-md-3">
        <?=Html::activeLabel($model, 'account_no', ['class' => 'control-label'])?>
    </div>
    <div class="col-md-6">
        <?=Html::activeTextInput($model, 'account_no', ['class' => 'form-control input-sm'])?>
        <?=Html::error($model, 'account_no', ['class' => 'error-block'])?>
    </div>
</div>

<div class="form-group">
    <div class="col-md-3">
        <?=Html::activeLabel($model, 'bank_code', ['class' => 'control-label'])?>
    </div>
    <div class="col-md-6">
        <?=Html::activeTextInput($model, 'bank_code', ['class' => 'form-control input-sm'])?>
        <?=Html::error($model, 'bank_code', ['class' => 'error-block'])?>
    </div>
</div>

<div class="form-group">
    <div class="col-md-3">
        <?=Html::activeLabel($model, 'iban', ['class' => 'control-label'])?>
    </div>
    <div class="col-md-6">
        <?=Html::activeTextInput($model, 'iban', ['class' => 'form-control input-sm'])?>
        <?=Html::error($model, 'iban', ['class' => 'error-block'])?>
    </div>
</div>

<div class="form-group">
    <div class="col-md-3">
        <?=Html::activeLabel($model, 'bic', ['class' => 'control-label'])?>
    </div>
    <div class="col-md-6">
        <?=Html::activeTextInput($model, 'bic', ['class' => 'form-control input-sm'])?>
        <?=Html::error($model, 'bic', ['class' => 'error-block'])?>
    </div>
</div>

<div class="form-group">
    <div class="col-md-3">
        <?=Html::activeLabel($model, 'account_holder', ['class' => 'control-label'])?>
    </div>
    <div class="col-md-6">
        <?=Html::activeTextInput($model, 'account_holder', ['class' => 'form-control input-sm'])?>
        <?=Html::error($model, 'account_holder', ['class' => 'error-block'])?>
    </div>
</div>
