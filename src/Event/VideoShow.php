<?php
namespace SK\VideoModule\Event;

use yii\base\Event;

class VideoShow extends Event
{
    public int $id = 0;
    public string $slug = '';
}
