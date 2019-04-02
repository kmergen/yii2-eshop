<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use kmergen\eshop\helpers\Cart;
use yii\helpers\ArrayHelper;
use kmergen\widgets\CharCounter;
use tigrov\intldata\Country;
use yii\helpers\Url;
use dosamigos\typeahead\Bloodhound;
use dosamigos\typeahead\TypeAhead;
use kmergen\eshop\CheckoutAsset;

/**
 * @var yii\web\View $this
 * @var common\models\Ad $model
 * @var yii\bootstrap\ActiveForm $form
 * @var kmergen\eshop\models\Order $order
 */

$this->title = Yii::t('app/eshop', 'Checkout Complete');
$this->params['breadcrumbs'][] = $this->title;
$profilingResults = Yii::getLogger()->getProfiling();
?>

<h1>Vielen Dank für Ihre Bestellung</h1>
<p>Ihre Order-Nr. lautet: <?= $order->id ?></p>
<p><?= Html::a('zurück', Yii::$app->user->returnUrl) ?></p>

<?php foreach ($profilingResults as $result): ?>

<p><?= $result['info'] . ': ' . ($result['du4
ration'] * 1000) . ' ms' ?></p>

<?php endforeach ?>




