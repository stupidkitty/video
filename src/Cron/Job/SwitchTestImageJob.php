<?php
namespace SK\VideoModule\Cron\Job;

use RS\Component\Core\Cron\CronJobInterface;
use SK\VideoModule\Service\Rotator as RotatorService;

/**
 * SwitchTestImage завершает тестирование тумбы, если у нее набрались просмотры
 */
class SwitchTestImageJob implements CronJobInterface
{
    /**
     * Меняет статус у тумб в категории, которые прошли тестирование.
     */
    public function run()
    {
        $rotatorService = new RotatorService();
        $rotatorService->markAsTestedRows();
    }
}
