<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Category;
use SK\CronModule\Handler\HandlerInterface;

class ClearOldStats implements HandlerInterface
{
    public function run()
    {
        $rotator = new Category();
        $rotator->clearOldStats();
    }
}
