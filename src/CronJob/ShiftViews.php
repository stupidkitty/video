<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Rotator\ShiftHistoryCheckpoint;
use App\Infrastructure\Cron\HandlerInterface;

class ShiftViews implements HandlerInterface
{
    public function run(): void
    {
        $handler = \Yii::$container->get(ShiftHistoryCheckpoint::class);
        $handler->handle();
    }
}
