<?php
$this->breadcrumbs=array(
	'Article Categories'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List ArticleCategory', 'url'=>array('index')),
	array('label'=>'Manage ArticleCategory', 'url'=>array('admin')),
);
?>

<h1>Create ArticleCategory</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>