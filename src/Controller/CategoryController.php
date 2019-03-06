<?php
namespace SK\VideoModule\Controller;

use Yii;
use yii\data\Sort;
use yii\web\Request;
use yii\web\Controller;
use yii\filters\PageCache;
use yii\filters\VerbFilter;
use SK\VideoModule\Model\Video;
use yii\data\ActiveDataProvider;
use SK\VideoModule\Model\Category;
use yii\base\ViewContextInterface;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Model\VideosCategoriesMap;
use RS\Component\Core\Filter\QueryParamsFilter;
use SK\VideoModule\Provider\RotateVideoProvider;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\EventSubscriber\VideoSubscriber;

/**
 * CategoryController implements the CRUD actions for Videos model.
 */
class CategoryController extends Controller implements ViewContextInterface
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'queryParams' => [
                'class' => QueryParamsFilter::class,
                'actions' => [
                    'index' => ['id', 'slug', 'page', 'o', 't'],
                    'date' => ['id', 'slug', 'page', 't'],
                    'views' => ['id', 'slug', 'page', 't'],
                    'likes' => ['id', 'slug', 'page', 't'],
                    'ctr' => ['id', 'slug', 'page', 't'],
                    'all-categories' => ['sort'],
                ],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) Yii::$container->get(SettingsInterface::class)->get('enable_page_cache', false),
                //'only' => ['index', 'ctr', 'list-all'],
                'duration' => 600,
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT MAX(`published_at`) FROM `videos` WHERE `published_at` <= NOW()',
                ],
                'variations' => [
                    Yii::$app->language,
                    $this->action->id,
                    $this->getRequest()->get('id', 0),
                    $this->getRequest()->get('slug', ''),
                    $this->getRequest()->get('page', 1),
                    $this->getRequest()->get('o', ''),
                    $this->getRequest()->get('t', 'all-time'),
                    $this->isMobile(),
                ],
            ],
        ];
    }

    /**
     * Переопределяет дефолтный путь шаблонов модуля.
     * Путь задается в конфиге модуля, в компонентах приложения.
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->module->getViewPath();
    }

    /**
     * Заметка.Можно считать клики в категорию по входу в первую страницу,
     * но в таком случае придется делать запрос на поиск по слагу.
     * Попробовать решить этот момент.
     */

    /**
     * Показывает список видео роликов текущей категории.
     *
     * @return mixed
     */
    public function actionIndex($id = 0, $slug = '', $page = 1, $o = 'date', $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        if (0 !== (int) $id) {
            $category = $this->findById($id);
        } elseif (!empty($slug)) {
            $category = $this->findBySlug($slug);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ('ctr' === $o) {
            $dataProvider = new RotateVideoProvider([
                'pagination' => [
                    'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                    'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                    'forcePageParam' => false,
                ],
                'sort' => $this->buildSort(),
                'category_id' => $category['category_id'],
                'testPerPagePercent' => (int) $settings->get('test_items_percent', 15, 'videos'),
                'testVideosStartPosition' => (int) $settings->get('test_items_start', 3, 'videos'),
                'datetimeLimit' => $t,
            ]);
        } else {
            $query = $this->buildInitialQuery($category, $t);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'totalCount' => $query->cachedCount(),
                'pagination' => [
                    'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                    'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                    'forcePageParam' => false,
                ],
                'sort' => $this->buildSort(),
            ]);
        }

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'images_ids' => array_column($videos, 'image_id'),
                    'page' => $page,
                ]
            );
        }

        return $this->render('category_videos', [
            'page' => $page,
            'sort' => $o,
            'settings' => $settings,
            'category' => $category,
            'videos' => $videos,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Показывает список видео роликов текущей категории осортированных по дате добавления.
     *
     * @return mixed
     */
    public function actionDate($id = 0, $slug = '', $page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        if (0 !== (int) $id) {
            $category = $this->findById($id);
        } elseif (!empty($slug)) {
            $category = $this->findBySlug($slug);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $query = $this->buildInitialQuery($category, $t);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $query->cachedCount(),
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
            ],
            'sort' => [
                'defaultOrder' => [
                    'published_at' => SORT_DESC,
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'images_ids' => array_column($videos, 'image_id'),
                    'page' => $page,
                ]
            );
        }

        return $this->render('category_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'category' => $category,
            'videos' => $videos,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Показывает список видео роликов текущей категории осортированных по просмортрам.
     *
     * @return mixed
     */
    public function actionViews($id = 0, $slug = '', $page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        if (0 !== (int) $id) {
            $category = $this->findById($id);
        } elseif (!empty($slug)) {
            $category = $this->findBySlug($slug);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $query = $this->buildInitialQuery($category, $t);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $query->cachedCount(),
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
            ],
            'sort' => [
                'defaultOrder' => [
                    'views' => SORT_DESC,
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'images_ids' => array_column($videos, 'image_id'),
                    'page' => $page,
                ]
            );
        }

        return $this->render('category_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'category' => $category,
            'videos' => $videos,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Показывает список видео роликов текущей категории осортированных по лайкам.
     *
     * @return mixed
     */
    public function actionLikes($id = 0, $slug = '', $page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        if (0 !== (int) $id) {
            $category = $this->findById($id);
        } elseif (!empty($slug)) {
            $category = $this->findBySlug($slug);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $query = $this->buildInitialQuery($category, $t);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $query->cachedCount(),
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
            ],
            'sort' => [
                'defaultOrder' => [
                    'likes' => SORT_DESC,
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'images_ids' => array_column($videos, 'image_id'),
                    'page' => $page,
                ]
            );
        }

        return $this->render('category_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'category' => $category,
            'videos' => $videos,
            'pagination' => $pagination,
        ]);
    }


    /**
     * List videos in category ordered by ctr
     *
     * @return mixed
     */
    public function actionCtr($id = 0, $slug = '', $page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        if (0 !== (int) $id) {
            $category = $this->findById($id);
        } elseif (!empty($slug)) {
            $category = $this->findBySlug($slug);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = new RotateVideoProvider([
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
            ],
            'sort' => [
                'sortParam' => 'o',
                'attributes' => [
                    'ctr' => [ // top rated
                        'asc' => ['vs.ctr' => SORT_DESC],
                        'desc' => ['vs.ctr' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ],
                'defaultOrder' => [
                    'ctr' => [ // top rated
                        'vs.ctr' => SORT_DESC,
                    ],
                ],
            ],
            'category_id' => $category['category_id'],
            'testPerPagePercent' => (int) $settings->get('test_items_percent', 15, 'videos'),
            'testVideosStartPosition' => (int) $settings->get('test_items_start', 3, 'videos'),
        ]);

        $dataProvider->prepare();

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'images_ids' => array_column($videos, 'image_id'),
                    'page' => $page,
                ]
            );
        }

        return $this->render('category_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'category' => $category,
            'videos' => $videos,
            'pagination' => $pagination,
        ]);
    }

    /**
     * List all categories
     *
     * @return mixed
     */
    public function actionAllCategories($sort = '') // Переименовать этот метод в AllCategories (template too)
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $sort = new Sort([
            'attributes' => [
                'abc' => [
                    'asc' => ['title' => SORT_ASC],
                    'desc' => ['title' => SORT_ASC],
                    'default' => SORT_ASC,
                    'label' => Yii::t('videos', 'title'),
                ],
                'mv' => [
                    'asc' => ['last_period_clicks' => SORT_DESC],
                    'desc' => ['last_period_clicks' => SORT_DESC],
                    'default' => SORT_DESC,
                    'label' => Yii::t('videos', 'popular'),
                ],
                'vn' => [
                    'asc' => ['videos_num' => SORT_DESC],
                    'desc' => ['videos_num' => SORT_DESC],
                    'default' => SORT_DESC,
                    'label' => Yii::t('videos', 'number_of_videos'),
                ],
            ],
            'defaultOrder' => [
                'mv' => SORT_DESC,
            ],
        ]);

        $categories = Category::find()
            ->select(['category_id', 'slug', 'image', 'title', 'description', 'param1', 'param2', 'param3', 'videos_num'])
            ->where(['enabled' => 1])
            ->orderBy($sort->getOrders())
            ->asArray()
            ->all();

        return $this->render('all_categories', [
            'categories' => $categories,
            'settings' => $settings,
            'sort' => $sort,
        ]);
    }

    /**
     * Find category by slug
     *
     * @param string $slug
     *
     * @return Category
     *
     * @throws NotFoundHttpException
     */
    public function findBySlug($slug)
    {
        $category = Category::find()
            ->where(['slug' => $slug, 'enabled' => 1])
            ->asArray()
            ->one();

        if (null === $category) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $category;
    }

    /**
     * Find category by id
     *
     * @param integer $id
     *
     * @return Category
     *
     * @throws NotFoundHttpException
     */
    public function findById($id)
    {
        $category = Category::find()
            ->where(['category_id' => $id, 'enabled' => 1])
            ->asArray()
            ->one();

        if (null === $category) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $category;
    }

    protected function buildInitialQuery($category, $t)
    {
        $query = Video::find()
            ->select(['v.video_id', 'v.image_id', 'v.slug', 'v.title', 'v.orientation', 'v.duration', 'v.likes', 'v.dislikes', 'v.comments_num', 'v.views', 'v.template', 'v.published_at'])
            ->alias('v')
            ->innerJoin(['vcm' => VideosCategoriesMap::tableName()], 'v.video_id = vcm.video_id')
            ->with(['categories' => function ($query) {
                $query->select(['category_id', 'title', 'slug', 'h1'])
                    ->where(['enabled' => 1]);
            }])
            ->with(['poster' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url']);
            }]);

        if ('all-time' === $t) {
            $query->untilNow();
        } elseif ($this->isValidRange($t)) {
            $query->rangedUntilNow($t);
        }

        $query
            ->onlyActive()
            ->andwhere(['vcm.category_id' => $category['category_id']])
            ->asArray();

        return $query;
    }

    protected function buildSort()
    {
        return new Sort([
            'sortParam' => 'o',
            'attributes' => [
                'date' => [
                    'asc' => ['v.published_at' => SORT_DESC],
                    'desc' => ['v.published_at' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
                'mv' => [
                    'asc' => ['v.views' => SORT_DESC],
                    'desc' => ['v.views' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
                'tr' => [
                    'asc' => ['v.likes' => SORT_DESC],
                    'desc' => ['v.likes' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
                'ctr' => [ // top rated
                    'asc' => ['vs.ctr' => SORT_DESC],
                    'desc' => ['vs.ctr' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
            ],
            'defaultOrder' => [
                'date' => [
                    'v.published_at' => SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Проверяет корректность параметра $t в экшене контроллера.
     * Значения: daily, weekly, monthly, early, all_time
     *
     * @param string $time Ограничение по времени.
     *
     * @return string.
     *
     * @throws NotFoundHttpException
     */
    protected function isValidRange($time)
    {
        if (in_array($time, ['daily', 'weekly', 'monthly', 'yearly', 'all-time'])) {
            return true;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Detect user is mobile device
     *
     * @return boolean
     */
    protected function isMobile()
    {
        $deviceDetect = Yii::$container->get('device.detect');
        
        return $deviceDetect->isMobile() || $deviceDetect->isTablet();
    }

    /**
     * Get request class form DI container
     *
     * @return \yii\web\Request
     */
    protected function getRequest()
    {
        return Yii::$container->get(Request::class);
    }
}
