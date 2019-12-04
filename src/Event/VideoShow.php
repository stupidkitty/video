<?php
namespace SK\VideoModule\Event;

use yii\base\Event;

class VideoShow extends Event
{
    public $id = 0;
    public $slug = '';
}
