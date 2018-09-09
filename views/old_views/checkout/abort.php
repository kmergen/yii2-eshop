<?php
use yii\helpers\Html;

$this->title='Checkout Abort';
?>

<h1>Abbruch des Bestellvorganges</h1>

<div>
Sie haben Ihren Bestellvorgang abgebrochen.
Ihre Produkte bleiben im Warenkorb. Sie können zu eimem späteren Zeitpunkt Ihre Bestellung wiederholen.

<div><?= Html::a('Erneut zur Kasse',['index'])?></div>
<div><?= Html::a('Zurück zum Shop',['/shop'])?></div>
</div>