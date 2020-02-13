<?php
namespace SK\VideoModule\Command;

use yii\console\Controller;
use SK\VideoModule\Service\Rotator;

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
     */
    public function actionMarkTested()
    {
        $rotator = new Rotator;
        $rotator->markAsTestedRows();
    }

    /**
     * Смещает указатель истори просмотров на следующий.
     *
     * @return void
     */
    public function actionShiftViews()
    {
        $rotator = new Rotator;
        $rotator->shiftHistoryCheckpoint();
    }

    /**
     * Сброс N видео для продолжения ротации если категория отротирована.
     *
     * @return void
     */
    public function actionResetTested()
    {
        $rotator = new Rotator;
        $rotator->resetOldTestedVideos();
    }

    /**
     * Сброс отротированных видео по показам.
     *
     * @return void
     */
    public function actionResetByShows()
    {
        $rotator = new Rotator;
        $rotator->cyclicResetByShows();
    }
}
