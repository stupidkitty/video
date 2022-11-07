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
     * @param MarkAsTestedThumbs $handler
     * @return void
     * @throws Exception
     */
    public function actionMarkTested(MarkAsTestedThumbs $handler): void
    {
        $handler->handle();
    }

    /**
     * Смещает указатель истори просмотров на следующий.
     *
     * @param ShiftHistoryCheckpoint $handler
     * @return void
     */
    public function actionShiftViews(ShiftHistoryCheckpoint $handler): void
    {
        $handler->handle();
    }

    /**
     * Сброс N видео для продолжения ротации если категория отротирована.
     *
     * @param ResetFields $handler
     * @return void
     * @throws Exception
     */
    public function actionResetTested(ResetFields $handler): void
    {
        $handler->resetOldTestedVideos();
    }

    /**
     * Сброс отротированных видео по показам.
     *
     * @param ResetFields $handler
     * @return void
     */
    public function actionResetByShows(ResetFields $handler): void
    {
        $handler->cyclicResetByShows();
    }
}
