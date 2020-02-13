<?php
namespace SK\VideoModule\Admin\Controller;

use SK\VideoModule\Model\Image;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosCategories;
use SK\VideoModule\Model\VideosRelatedMap;
use SK\VideoModule\Service\Category as CategoryService;
use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * ToolsController это всякие инструменты.
 */
class ToolsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'clear-stats' => ['post'],
                    'random-date' => ['post'],
                    'clear-videos' => ['post'],
                    'clear-related' => ['post'],
                    'recalculate-categories-videos' => ['post'],
                    'set-categories-thumbs' => ['post'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'only' => [
                    'clear-stats',
                    'random-date',
                    'clear-videos',
                    'clear-related',
                    'recalculate-categories-videos',
                    'set-categories-thumbs',
                ],
                'formatParam' => '_format',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Выводит форму с различными действиями для видео роликов.
     * @return mixed
     */
    public function actionIndex()
    {

        return $this->render('index', [
        ]);
    }

    /**
     * Очищает статистику по видео (показы, просмотры и т.д.)
     * @return json
     */
    public function actionClearStats()
    {
        try {
            // Очистка статистики тумб
            VideosCategories::updateAll([
                'is_tested' => 0,
                'tested_at' => null,
                'shows_before_reset' => 0,
                'current_index' => 0,
                'current_shows' => 0,
                'current_clicks' => 0,
                'shows0' => 0,
                'clicks0' => 0,
                'shows1' => 0,
                'clicks1' => 0,
                'shows2' => 0,
                'clicks2' => 0,
                'shows3' => 0,
                'clicks3' => 0,
                'shows4' => 0,
                'clicks4' => 0,
                'shows5' => 0,
                'clicks5' => 0,
                'shows6' => 0,
                'clicks6' => 0,
                'shows7' => 0,
                'clicks7' => 0,
                'shows8' => 0,
                'clicks8' => 0,
                'shows9' => 0,
                'clicks9' => 0,
            ]);

            // Очитска просмотров, лайков, дизлайков.
            Video::updateAll([
                'likes' => 0,
                'dislikes' => 0,
                'views' => 0,
            ]);

            return [
                'message' => 'Videos statistic cleared',
            ];
        } catch (\Exception $e) {
            return [
                'error' => [
                    'code' => 1,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return json
     */
    public function actionRandomDate()
    {
        try {
            // Рандом для видео в таблице `videos`
            Video::updateAll([
                'published_at' => new Expression('FROM_UNIXTIME(UNIX_TIMESTAMP(NOW()) - FLOOR(0 + (RAND() * 31536000)))'),
            ]);

            return [
                'message' => 'All videos published date randomized',
            ];
        } catch (\Exception $e) {
            return [
                'error' => [
                    'code' => 1,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Удаляет все видео.
     *
     * @return json
     */
    public function actionClearVideos()
    {
        try {
            // Очищаем релатеды.
            Yii::$app->db->createCommand()
                ->truncateTable(VideosRelatedMap::tableName())
                ->execute();

            // Очищаем релатеды.
            Yii::$app->db->createCommand()
                ->truncateTable(VideosCategories::tableName())
                ->execute();

            // Удаляем фотки
            Yii::$app->db->createCommand()
                ->truncateTable(Image::tableName())
                ->execute();

            // Удаляем видео
            Yii::$app->db->createCommand()
                ->truncateTable(Video::tableName())
                ->execute();

            return [
                'message' => 'All videos published date randomized',
            ];
        } catch (\Exception $e) {
            return [
                'error' => [
                    'code' => 1,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }
    /**
     * Очищает таблицу "похожие видео".
     *
     * @return json
     */
    public function actionClearRelated()
    {
        try {
            Yii::$app->db->createCommand()
                ->truncateTable(VideosRelatedMap::tableName())
                ->execute();

            return [
                'message' => 'Related videos cleared',
            ];
        } catch (\Exception $e) {
            return [
                'error' => [
                    'code' => 1,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }
    /**
     * Очищает таблицу "похожие видео".
     *
     * @return json
     */
    public function actionRecalculateCategoriesVideos()
    {
        try {
            $categoryService = new CategoryService();
            $categoryService->countVideos();

            return [
                'message' => 'All active videos counted in categories',
            ];
        } catch (\Exception $e) {
            return [
                'error' => [
                    'code' => 1,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }
    /**
     * Установка тумб у категорий по данным ротации
     *
     * @return json
     */
    public function actionSetCategoriesThumbs()
    {
        try {
            $categoryService = new CategoryService();
            $categoryService->assignCoverImages();

            return [
                'message' => 'New categories thumbs set up',
            ];
        } catch (\Exception $e) {
            return [
                'error' => [
                    'code' => 1,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }
}
