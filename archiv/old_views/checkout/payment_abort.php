<?php
use yii\helpers\Html;

$this->title='Checkout Payment Abort';

?>

<h1>Zahlung nicht erfolgreich</h1>

<div>
Die Zahlung konnte nicht ausgeführt werden.
Ihre Produkte bleiben im Warenkorb. Sie können zu eimem späteren Zeitpunkt den Zahlungsvorgang wiederholen.

<div><?=Html::a('Erneut zur Kasse',['index'])?></div>
<div><?=CHtml::link('Zur&uuml;ck zum Shop',['/shop'])?></div>
</div>