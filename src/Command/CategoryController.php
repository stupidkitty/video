<?php
namespace SK\VideoModule\Command;

use yii\console\Controller;
use SK\VideoModule\Service\Category;

/**
 * This command echoes the first argument that you have entered.
 */
class CategoryController extends Controller
{
    /**
     * Пересчитывает популярность категории.
     */
    public function actionUpdatePopularity()
    {
        $categoryService = new Category();
        $categoryService->updatePopularity();
    }

    /**
     * Пересчитывает активные Галереи в категория.
     */
    public function actionCountVideos()
    {
        $categoryService = new Category();
        $categoryService->countVideos();
    }

    /**
     * Устанавливает главные тумбы у категории
     */
    public function actionAssignCoverImages()
    {
        $categoryService = new Category();
        $categoryService->assignCoverImages();
    }

    /**
     * Удаляет старую статистику по кликам в категорию
     */
    public function actionClearOldStats()
    {
        $categoryService = new Category();
        $categoryService->clearOldStats();
    }
}
