<?php
$this->breadcrumbs=array(
	'ArticleOld Categories',
);

$this->menu=array(
	array('label'=>'Create ArticleCategory', 'url'=>array('create')),
	array('label'=>'Manage ArticleCategory', 'url'=>array('admin')),
);
?>

<h1>Article Categories</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
