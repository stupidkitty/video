<?php
namespace SK\VideoModule\Command;

use SK\VideoModule\Rotator\MarkAsTestedThumbs;
use SK\VideoModule\Rotator\ResetFields;
use SK\VideoModule\Rotator\ShiftHistoryCheckpoint;
use yii\console\Controller;
use yii\db\Exception;

/**
 * This command echoes the first argument that you have entered.
 */
class RotatorController extends Controller
{
    /**
     * Индекс
     */
    public function actionIndex()
    {
        echo 'Hello';
    }

    /**
     * Помечает тумбы как тестированные в статистике.
     *
     * @return void
     * @throws Exception
     */
    public function actionMarkTested(MarkAsTestedThumbs $handler)
    {
        $handler->handle();
    }

    /**
     * Смещает указатель истори просмотров на следующий.
     *
     * @return void
     */
    public function actionShiftViews(ShiftHistoryCheckpoint $handler)
    {
        $handler->handle();
    }

    /**
     * Сброс N видео для продолжения ротации если категория отротирована.
     *
     * @return void
     * @throws Exception
     */
    public function actionResetTested(ResetFields $handler)
    {
        $handler->resetOldTestedVideos();
    }

    /**
     * Сброс отротированных видео по показам.
     *
     * @return void
     */
    public function actionResetByShows(ResetFields $handler)
    {
        $handler->cyclicResetByShows();
    }
}
