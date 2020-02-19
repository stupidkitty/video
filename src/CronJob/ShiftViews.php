<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Rotator;
use SK\CronModule\Handler\HandlerInterface;

class ShiftViews implements HandlerInterface
{
    public function run()
    {
        $rotator = new Rotator;
        $rotator->shiftHistoryCheckpoint();
    }
}
