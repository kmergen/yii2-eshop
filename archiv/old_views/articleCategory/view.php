<?php
$this->breadcrumbs=array(
	'ArticleOld Categories'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List ArticleCategory', 'url'=>array('index')),
	array('label'=>'Create ArticleCategory', 'url'=>array('create')),
	array('label'=>'Update ArticleCategory', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete ArticleCategory', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage ArticleCategory', 'url'=>array('admin')),
);
?>

<h1>View ArticleCategory #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
	),
)); ?>
