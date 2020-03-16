<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Rotator;
use SK\CronModule\Handler\HandlerInterface;

class MarkTestedThumbs implements HandlerInterface
{
    public function run(): void
    {
        $rotator = new Rotator;
        $rotator->markAsTestedRows();
    }
}
