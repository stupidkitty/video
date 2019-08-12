<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Category;
use SK\CronModule\Handler\HandlerInterface;

class AssignCoverImages implements HandlerInterface
{
    public function run()
    {
        $category = new Category();
        $category->assignCoverImages();
    }
}
