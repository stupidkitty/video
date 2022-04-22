<?php

namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Category as CategoryService;
use App\Infrastructure\Cron\HandlerInterface;
use yii\db\Exception;

class CountVideos implements HandlerInterface
{
    /**
     * @throws Exception
     */
    public function run(): void
    {
        $category = new CategoryService();
        $category->countVideos();
    }
}
