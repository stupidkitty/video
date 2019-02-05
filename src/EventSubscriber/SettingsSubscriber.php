<?php
namespace SK\VideoModule\EventSubscriber;

use Yii;

class SettingsSubscriber
{
    public static function onMenuInit($event)
    {
        $event->sender->addItem([
            'label' => 'Видео',
            'group' => 'modules',
            'url' => ['/admin/videos/settings/index'],
            'icon' => '<i class="fa fa-video-camera"></i>',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'videos' && Yii::$app->controller->id === 'settings')
        ]);
    }
}
