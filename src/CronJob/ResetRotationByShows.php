<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Rotator;
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
        $rotator = new Rotator;
        $rotator->cyclicResetByShows();
    }
}
