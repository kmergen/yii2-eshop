<?php
use yii\helpers\Html;

$this->title = 'Checkout Success';
?>

<h1>Vielen Dank für Ihre Bestellung</h1>

<div>
    Ihre Bestellung wurde erfolgreich ausgeführt.
    <div><?=Html::a('Zurück zum Shop', ['/shop'])?></div>
</div>