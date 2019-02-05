<?php
namespace SK\VideoModule\Cron\Job;

use RS\Component\Core\Cron\CronJobInterface;
use SK\VideoModule\Service\Category as CategoryService;

/**
 * RemoveOldDataJob удаление различных устаревших данных.
 */
class RemoveOldDataJob implements CronJobInterface
{
    /**
     * Удаляет старую статистику по кликам в категорию
     * 
     * @return void
     */
    public function run()
    {
        $categoryService = new CategoryService();
        $categoryService->clearOldStats();
    }
}
