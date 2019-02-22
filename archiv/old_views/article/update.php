<?php
$this->breadcrumbs=array(
	'Articles'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List ArticleOld', 'url'=>array('index')),
	array('label'=>'Create ArticleOld', 'url'=>array('create')),
	array('label'=>'View ArticleOld', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage ArticleOld', 'url'=>array('admin')),
);
?>

<h1>Update Article <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>
