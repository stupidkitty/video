<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Rotator as RotatorService;
use SK\CronModule\Handler\HandlerInterface;

class ShiftViews implements HandlerInterface
{
    public function run()
    {
        $rotator = new RotatorService();
        $rotator->shiftHistoryCheckpoint();
    }
}
