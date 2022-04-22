<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Rotator\ResetFields;
use App\Infrastructure\Cron\HandlerInterface;

/**
 * Сброс отротированных видео по показам.
 *
 * @return void
 */
class ResetRotationByShows implements HandlerInterface
{
    public function run(): void
    {
        $handler = \Yii::$container->get(ResetFields::class);
        $handler->cyclicResetByShows();
    }
}
