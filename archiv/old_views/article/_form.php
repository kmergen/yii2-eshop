<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'ArticleOld-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>
	<div class="row">
	  <?php echo $categoryModels=ArticleCategory::model()->findAll(); ?>
	  <?php echo $form->labelEx($model,'category_id'); ?>
		<?php echo $form->DropDownList($model,'category_id',CHtml::listData($categoryModels,'id','name')); ?>
		<?php echo $form->error($model,'category_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'sku'); ?>
		<?php echo $form->textField($model,'sku',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'sku'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>150)); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'sell_price'); ?>
		<?php echo $form->textField($model,'sell_price',array('size'=>15,'maxlength'=>15)); ?>
		<?php echo $form->error($model,'sell_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'default_qty'); ?>
		<?php echo $form->textField($model,'default_qty'); ?>
		<?php echo $form->error($model,'default_qty'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ordering'); ?>
		<?php echo $form->textField($model,'ordering'); ?>
		<?php echo $form->error($model,'ordering'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
