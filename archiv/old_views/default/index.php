<?php

use app\modules\eshop\Module;

$this->title = Module::t('Shop');
$this->params['breadcrumbs'][] = $this->title;

?>

<h1>Shop</h1>

<p>Sie sind auf der Shopseite von <?= \Yii::$app->name ?>.</p>

