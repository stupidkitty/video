<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Video;
use SK\CronModule\Handler\HandlerInterface;

class UpdateMaxCtr implements HandlerInterface
{
    public function run()
    {
        $rotator = new Video();
        $rotator->updateMaxCtr();
    }
}
