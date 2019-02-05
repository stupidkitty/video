<?php
namespace SK\VideoModule\Cron\Job;

use RS\Component\Core\Cron\CronJobInterface;
use SK\VideoModule\Service\Category as CategoryService;

/**
 * CategoryClicksUpdateJob Пересчитывает клики за последнюю неделю и обновляет в категориях.
 */
class CategoryClicksUpdateJob implements CronJobInterface
{
    /**
     * Выполняет полезную работу.
     */
    public function run()
    {
        $categoryService = new CategoryService();
        $categoryService->updatePopularity();
    }
}
