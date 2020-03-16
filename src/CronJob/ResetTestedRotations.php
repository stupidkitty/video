<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Rotator;
use SK\CronModule\Handler\HandlerInterface;

class ResetTestedRotations implements HandlerInterface
{
    public function run(): void
    {
        $rotator = new Rotator;
        $rotator->resetOldTestedVideos();
    }
}
