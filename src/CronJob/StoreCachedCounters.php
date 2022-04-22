<?php

namespace SK\VideoModule\CronJob;

use App\Infrastructure\Cron\HandlerInterface;
use SK\VideoModule\Video\UseCase\StoreCachedCountersIntoDb;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

class StoreCachedCounters implements HandlerInterface
{
    /**
     * @throws \Throwable
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function run(): void
    {
        $handler = \Yii::$container->get(StoreCachedCountersIntoDb::class);
        $handler->store();
    }
}
