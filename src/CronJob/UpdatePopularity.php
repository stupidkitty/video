<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Category as CategoryService;
use SK\CronModule\Handler\HandlerInterface;

class UpdatePopularity implements HandlerInterface
{
    public function run()
    {
        $category = new CategoryService();
        $category->updatePopularity();
    }
}
