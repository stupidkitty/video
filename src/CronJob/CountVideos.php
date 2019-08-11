<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Category;
use SK\CronModule\Handler\HandlerInterface;

class CountVideos implements HandlerInterface
{
    public function run()
    {
        $rotator = new Category();
        $rotator->countVideos();
    }
}
