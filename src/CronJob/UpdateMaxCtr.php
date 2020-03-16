<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Video as VideoService;
use SK\CronModule\Handler\HandlerInterface;

class UpdateMaxCtr implements HandlerInterface
{
    public function run(): void
    {
        $video = new VideoService();
        $video->updateMaxCtr();
    }
}
