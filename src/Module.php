<?php

namespace SK\VideoModule;

use Yii;
use yii\base\Module as BaseModule;
use yii\i18n\PhpMessageSource;
use yii\console\Application as ConsoleApplication;

/**
 * This is the main module class of the video extension.
 */
class Module extends BaseModule
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'SK\VideoModule\Controller';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'main/index';

     /**
      * @inheritdoc
      */
     public $layoutPath = '';

     /**
      * @inheritdoc
      */
     public function __construct($id, $parent = null, $config = [])
     {
         // дефолтный путь до папки темплейтов.
         $this->setViewPath(__DIR__ . '/Resources/views');

         require(__DIR__ . '/bootstrap.php');

         parent::__construct ($id, $parent, $config);
     }

    public function init()
    {
        parent::init();

        // контреллеры для консольных команд
        if (Yii::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'SK\VideoModule\Command';
        }

        // Переводы с языков.
        if (Yii::$app->has('i18n') && empty(Yii::$app->get('i18n')->translations['videos'])) {
            Yii::$app->get('i18n')->translations['videos'] = [
                'class' => PhpMessageSource::class,
                'basePath' => __DIR__ . '/Resources/i18n',
                'sourceLanguage' => 'en-US',
            ];
        }
    }
}
