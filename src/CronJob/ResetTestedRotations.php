<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Rotator;
use App\Infrastructure\Cron\HandlerInterface;

class ResetTestedRotations implements HandlerInterface
{
    public function run(): void
    {
        $rotator = new Rotator;
        $rotator->resetOldTestedVideos();
    }
}
