<?php
namespace SK\VideoModule\Command;

use yii\console\Controller;
use SK\VideoModule\Service\Rotator as RotatorService;

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
        $rotatorService = new RotatorService();
        $rotatorService->markAsTestedRows();
    }

    /**
     * Смещает указатель истори просмотров на следующий.
     *
     * @return void
     */
    public function actionShiftViews()
    {
        $rotatorService = new RotatorService();
        $rotatorService->shiftHistoryCheckpoint();
    }

    /**
     * Смещает указатель истори просмотров на следующий.
     *
     * @return void
     */
    public function actionResetTested()
    {
        $rotatorService = new RotatorService();
        $rotatorService->resetOldTestedVideos();
    }
}
