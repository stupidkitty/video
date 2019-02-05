<?php
namespace SK\VideoModule\Cron\Job;

use RS\Component\Core\Cron\CronJobInterface;
use SK\VideoModule\Service\Category as CategoryService;

/**
 * SetCategoriesThumbs устанавливает категорийные тумбы исходя из данных по цтр тумб к видео
 */
class SetCategoriesThumbsJob implements CronJobInterface
{
    /**
     * Устанавливает главную тумбу категории
     */
    public function run()
    {
        $categoryService = new CategoryService();
        $categoryService->assignCoverImages();
    }
}
