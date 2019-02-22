<?php
$this->breadcrumbs=array(
  'Shop'=>array('/Shop'),
	'EmptyCart',
);
?>

<h1>Leerer Einkaufswagen</h1>

<p>Ihr Einkaufswagen ist leer.</p>
<?php echo CHtml::link('zurück', $destination);?>

