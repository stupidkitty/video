<?php
namespace SK\VideoModule\Cron\Job;

use RS\Component\Core\Cron\CronJobInterface;
use SK\VideoModule\Service\Rotator as RotatorService;

/**
 * ShiftCheckpoint смещает чек поинт вправо, если просмотры достигли границы 1\5 от расчетного периода ЦТР
 */
class ShiftCheckpointJob implements CronJobInterface
{
    /**
     * Метод смещает контрольные точки у тумб. Всего контрольных точек пять.
     * Значит берем клики периода рекалькуляции цтр и раскидываем равномерно по пяти точкам.
     * Затем выберем все тумбы, которые достигли необходимого значения, обнулим счетчик и сместим вправо на следующую точку.
     */
    public function run()
    {
        $rotatorService = new RotatorService();
        $rotatorService->shiftHistoryCheckpoint();
    }
}
