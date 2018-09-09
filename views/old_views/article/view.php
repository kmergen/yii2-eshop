<?php
$this->breadcrumbs=array(
	'Articles'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List ArticleOld', 'url'=>array('index')),
	array('label'=>'Create ArticleOld', 'url'=>array('create')),
	array('label'=>'Update ArticleOld', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete ArticleOld', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage ArticleOld', 'url'=>array('admin')),
);
?>

<h1>View Article #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'sku',
		'title',
		'description',
		'sell_price',
		'default_qty',
		'ordering',
	),
)); ?>
