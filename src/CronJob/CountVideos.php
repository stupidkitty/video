<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Category as CategoryService;
use App\Infrastructure\Cron\HandlerInterface;

class CountVideos implements HandlerInterface
{
    public function run(): void
    {
        $category = new CategoryService();
        $category->countVideos();
    }
}
