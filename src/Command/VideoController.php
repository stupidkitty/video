<?php
namespace SK\VideoModule\Command;

use SK\VideoModule\Video\UseCase\StoreCachedCountersIntoDb;
use yii\console\Controller;
use SK\VideoModule\Service\Video as VideoService;

/**
 * This command echoes the first argument that you have entered.
 */
class VideoController extends Controller
{
    /**
     * Индекс
     */
    public function actionIndex()
    {
        echo 'Hello';
    }

    /**
     * Обновляет максимальный цтр видео.
     *
     * @return void
     */
    public function actionUpdateMaxCtr()
    {
        $videoService = new VideoService();
        $videoService->updateMaxCtr();
    }

    /**
     * @param StoreCachedCountersIntoDb $handler
     * @return void
     * @throws \Throwable
     */
    public function actionStoreCachedCounters(StoreCachedCountersIntoDb $handler): void
    {
        $handler->store();
    }
}
