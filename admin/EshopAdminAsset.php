<?php


namespace kmergen\eshop;
use yii\web\AssetBundle;



class EshopAsset extends AssetBundle
{
   	public $sourcePath = '@kmergen/eshop/assets';
	public $js = [
		'eshop.admin.min.js',
	];
	public $css = [
        'eshop.admin.min.css'
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
                'eshop.admin.js'
            ];
            $this->css = [
                'eshop.admin.css'
            ];
        }
    }

}

