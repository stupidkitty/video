<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Service\Video as VideoService;
use App\Infrastructure\Cron\HandlerInterface;

class UpdateMaxCtr implements HandlerInterface
{
    public function run(): void
    {
        $video = new VideoService();
        $video->updateMaxCtr();
    }
}
