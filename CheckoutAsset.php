<?php


namespace kmergen\eshop;
use yii\web\AssetBundle;



class CheckoutAsset extends AssetBundle
{
   	public $sourcePath = '@kmergen/eshop/assets';
	public $js = [
		'checkout.min.js',
	];

	public $depends = [
		'kmergen\eshop\EshopAsset',
	];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (YII_DEBUG) {
            $this->js = [
                'checkout.js'
            ];
        }
    }
}

