<?php

namespace kmergen\eshop\admin;

/**
 * Admin module definition class
 */
class Module extends \yii\base\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'kmergen\eshop\admin\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'dashboard/index';



    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //$this->setViewPath('@app/themes/adminlte/views');
        $this->registerTranslations();

        // custom initialization code goes here
    }

    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['backend'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath' => '@kmergen/eshop/admin/messages',
        ];
    }

}
