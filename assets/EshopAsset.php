<?php


namespace app\modules\eshop\assets;
use yii\web\AssetBundle;



class EshopAsset extends AssetBundle
{
   	public $sourcePath = '@app/modules/eshop/assets';
	public $js = [
		'eshop.js',
	];
	public $css = [
        'style.css'
	];
	public $depends = [
		//'app\assets\AppAsset',
	];
   
}

