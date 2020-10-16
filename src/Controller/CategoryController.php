<?php

namespace SK\VideoModule\Controller;

use RS\Component\Core\Filter\QueryParamsFilter;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\EventSubscriber\VideoSubscriber;
use SK\VideoModule\Form\FilterForm;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosCategories;
use SK\VideoModule\Provider\RotateVideoProvider;
use Yii;
use yii\base\ViewContextInterface;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\filters\PageCache;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

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
                    'index' => ['id', 'slug', 'page', 'o', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'date' => ['id', 'slug', 'page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'views' => ['id', 'slug', 'page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'likes' => ['id', 'slug', 'page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'ctr' => ['id', 'slug', 'page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'all-categories' => ['sort', 'orientation'],
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
                    \implode(':', \array_values($this->getRequest()->get())),
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

        $identify = (0 !== (int) $id) ? (int) $id : $slug;
        $category = $this->findByIdentify($identify);

        if ('ctr' === $o) {
            $dataProvider = new RotateVideoProvider([
                'pagination' => [
                    'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                    'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                    'forcePageParam' => false,
                    'validatePage' => false,
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
                    'validatePage' => false,
                ],
                'sort' => $this->buildSort(),
            ]);
        }

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($page > 1 && empty($videos)) {
            Yii::$app->response->statusCode = 404;
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'videos_ids' => \array_column($videos, 'video_id'),
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

        $identify = (0 !== (int) $id) ? (int) $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
        ]);
        $filterForm->load($this->getRequest()->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($category, $filterForm);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $query->cachedCount(),
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
                'validatePage' => false,
            ],
            'sort' => [
                'defaultOrder' => [
                    'published_at' => SORT_DESC,
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($page > 1 && empty($videos)) {
            Yii::$app->response->statusCode = 404;
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'videos_ids' => \array_column($videos, 'video_id'),
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
            'filterForm' => $filterForm,
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

        $identify = (0 !== (int) $id) ? (int) $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
        ]);
        $filterForm->load($this->getRequest()->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($category, $filterForm);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $query->cachedCount(),
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
                'validatePage' => false,
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
                    'videos_ids' => \array_column($videos, 'video_id'),
                    'page' => $page,
                ]
            );
        }

        if ($page > 1 && empty($videos)) {
            Yii::$app->response->statusCode = 404;
        }

        return $this->render('category_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'category' => $category,
            'videos' => $videos,
            'pagination' => $pagination,
            'filterForm' => $filterForm,
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

        $identify = (0 !== (int) $id) ? (int) $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
        ]);
        $filterForm->load($this->getRequest()->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($category, $filterForm);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $query->cachedCount(),
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
                'validatePage' => false,
            ],
            'sort' => [
                'defaultOrder' => [
                    'likes' => SORT_DESC,
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($page > 1 && empty($videos)) {
            Yii::$app->response->statusCode = 404;
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'videos_ids' => \array_column($videos, 'video_id'),
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
            'filterForm' => $filterForm,
        ]);
    }

    /**
     * List videos in category ordered by ctr
     *
     * @param int $id
     * @param string $slug
     * @param int $page
     * @param string $t
     * @param Response $response
     * @param SettingsInterface $settings
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionCtr(int $id = 0, string $slug = '', int $page = 1, string $t = 'all-time', Response $response, SettingsInterface $settings)
    {
        $identify = (0 !== (int) $id) ? (int) $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
        ]);
        $filterForm->load($this->getRequest()->get());
        $filterForm->isValid();

        $dataProvider = new RotateVideoProvider([
            'filterForm' => $filterForm,
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
                'validatePage' => false,
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

        if ($page > 1 && empty($videos)) {
            $response->statusCode = 404;
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(
                self::EVENT_AFTER_ACTION,
                [
                    VideoSubscriber::class,
                    'onShowCategoryThumbs'
                ],
                [
                    'category_id' => $category['category_id'],
                    'videos_ids' => \array_column($videos, 'video_id'),
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
            'filterForm' => $filterForm,
        ]);
    }

    /**
     * List all categories
     *
     * @param SettingsInterface $settings
     * @return mixed
     */
    public function actionAllCategories(SettingsInterface $settings) // Переименовать этот метод в AllCategories (template too)
    {
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
                'abc' => SORT_ASC,
            ],
        ]);

        $categories = Category::find()
            ->select(['category_id', 'slug', 'image', 'title', 'h1', 'description', 'param1', 'param2', 'param3', 'videos_num'])
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
     * Find category by primary key or by slug
     *
     * @param int|string $identify
     * @return array|Category|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public function findByIdentify($identify)
    {
        $query = Category::find()
            ->asArray();

        if (\is_integer($identify)) {
            $query->where(['category_id' => $identify]);
        } else {
            $query->where(['slug' => $identify]);
        }

        $category = $query
            ->andWhere(['enabled' => 1])
            ->one();

        if (null === $category) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $category;
    }

    protected function buildInitialQuery($category, $filterForm)
    {
        $query = Video::find()
            ->asThumbs()
            ->innerJoin(['vcm' => VideosCategories::tableName()], 'v.video_id = vcm.video_id')
            ->andwhere(['vcm.category_id' => $category['category_id']]);

        if ('all-time' === $filterForm->t) {
            $query->untilNow();
        } else {
            $query->rangedUntilNow($filterForm->t);
        }

        $query
            ->onlyActive()
            ->andFilterWhere(['v.orientation' => $filterForm->orientation])
            ->andFilterWhere(['>=', 'v.duration', $filterForm->durationMin])
            ->andFilterWhere(['<=', 'v.duration', $filterForm->durationMax])
            ->andFilterWhere(['v.is_hd' => $filterForm->isHd])
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
