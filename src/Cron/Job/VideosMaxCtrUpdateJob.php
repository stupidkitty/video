<?php
namespace SK\VideoModule\Cron\Job;

use RS\Component\Core\Cron\CronJobInterface;
use SK\VideoModule\Service\Video as VideoService;

/**
 * VideosMaxCtrUpdateJob Обновляет колонку `max_ctr` в таблице `videos`
 */
class VideosMaxCtrUpdateJob implements CronJobInterface
{
    /**
     * Обновляет максимальный цтр среди категорий и тумб.
     * 
     * @return void
     */
    public function run()
    {
        $videoService = new VideoService();
        $videoService->updateMaxCtr();
    }
}
