<?php
$this->breadcrumbs=array(
	'Articles'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List ArticleOld', 'url'=>array('index')),
	array('label'=>'Manage ArticleOld', 'url'=>array('admin')),
);
?>

<h1>Create Article</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>
