<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $order kmergen\eshop\models\Order */
/* @var $customer kmergen\eshop\models\Customer */
/* @var $module kmergen\eshop\Module */

?>
<p>Sehr geehrter Kunde,</p>

<p>vielen Dank für Ihre Bestellung in unserem Shop</p>

<p>Ihre Bestell-Nr. lautet: <?= $order->id ?></p>

<p>Ihre aktuellen Bestelldaten können Sie hier einsehen: <?= Html::a('Bestellung', ['/eshop/order/', 'order' => $order->id]) ?></p>

<p>Bei Rückfragen stehen wir Ihnen gerne zur Verfügung.</p>

<p>Mit freundlichen Grüßen</p>
<p>Ihr <?= $module->shopName ?> Team</p>
