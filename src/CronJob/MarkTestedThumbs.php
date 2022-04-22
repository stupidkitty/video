<?php

namespace SK\VideoModule\CronJob;

use SK\VideoModule\Rotator\MarkAsTestedThumbs;
use App\Infrastructure\Cron\HandlerInterface;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\di\NotInstantiableException;

class MarkTestedThumbs implements HandlerInterface
{
    /**
     * @throws NotInstantiableException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function run(): void
    {
        $handler = \Yii::$container->get(MarkAsTestedThumbs::class);
        $handler->handle();
    }
}
