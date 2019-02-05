<?php
namespace SK\VideoModule\Cron\Job;

use RS\Component\Core\Cron\CronJobInterface;
use SK\VideoModule\Service\Category as CategoryService;

/**
 * RecalculateVideosJob Пересчет активных видео в категории и обновление счетчиков у категории.
 */
class RecalculateVideosJob implements CronJobInterface
{
    /**
     * Выполняет полезную работу.
     */
    public function run()
    {
        $categoryService = new CategoryService();
        $categoryService->countVideos();
    }
}
