<?php


namespace kmergen\eshop;
use yii\web\AssetBundle;



class EshopAsset extends AssetBundle
{
   	public $sourcePath = '@kmergen/eshop/assets';
	public $js = [
		'eshop.min.js',
	];
	public $css = [
        'eshop.min.css'
	];
	public $depends = [
		//'app\assets\AppAsset',
	];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (YII_DEBUG) {
            $this->js = [
                'Eshop.js'
            ];
            $this->css = [
                'eshop.css'
            ];
        }
    }

}

