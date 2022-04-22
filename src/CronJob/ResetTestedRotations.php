<?php
namespace SK\VideoModule\CronJob;

use SK\VideoModule\Rotator\ResetFields;
use App\Infrastructure\Cron\HandlerInterface;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\di\NotInstantiableException;

class ResetTestedRotations implements HandlerInterface
{
    /**
     * @throws Exception
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function run(): void
    {
        $handler = \Yii::$container->get(ResetFields::class);
        $handler->resetOldTestedVideos();
    }
}
