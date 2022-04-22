<?php
namespace SK\VideoModule\Command;

use yii\base\InvalidConfigException;
use yii\console\Controller;
use SK\VideoModule\Service\Category;
use yii\db\Exception;

/**
 * This command echoes the first argument that you have entered.
 */
class CategoryController extends Controller
{
    /**
     * Пересчитывает популярность категории.
     *
     * @throws Exception
     */
    public function actionUpdatePopularity()
    {
        $categoryService = new Category();
        $categoryService->updatePopularity();
    }

    /**
     * Пересчитывает активные Галереи в категория.
     *
     * @throws Exception
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
     *
     * @throws InvalidConfigException
     */
    public function actionClearOldStats()
    {
        $categoryService = new Category();
        $categoryService->clearOldStats();
    }
}
