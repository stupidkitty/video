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
use SK\VideoModule\Query\VideoQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\ViewContextInterface;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\di\NotInstantiableException;
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
                'enabled' => (bool) $this->get(SettingsInterface::class)->get('enable_page_cache', false),
                //'only' => ['index', 'ctr', 'list-all'],
                'duration' => 600,
                'dependency' => [
                    'class' => TagDependency::class,
                    'tags' => 'videos:categories',
                ],
                'variations' => [
                    Yii::$app->language,
                    $this->action->id,
                    \implode(':', \array_values($this->get(Request::class)->get())),
                    $this->isMobile(),
                ],
            ],
        ];
    }

    /**
     * Get instance by tag name form DI container
     *
     * @param string $name
     * @return object
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    protected function get(string $name): object
    {
        return Yii::$container->get($name);
    }

    /**
     * Заметка.Можно считать клики в категорию по входу в первую страницу,
     * но в таком случае придется делать запрос на поиск по слагу.
     * Попробовать решить этот момент.
     */

    /**
     * Detect user is mobile device
     *
     * @return boolean
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    protected function isMobile(): bool
    {
        $deviceDetect = $this->get('device.detect');

        return $deviceDetect->isMobile();
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
     * Показывает список видео роликов текущей категории.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $id
     * @param string $slug
     * @param int $page
     * @param string $o
     * @param string $t
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $id = 0,
        string $slug = '',
        int $page = 1,
        string $o = 'date',
        string $t = 'all-time'
    ): string
    {
        $identify = (0 !== $id) ? $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
            'o' => $o
        ]);
        $filterForm->load($request->get());
        $filterForm->isValid();

        if ('ctr' === $filterForm->o) {
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
                'sort' => $this->buildSort(),
            ]);
        }

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
            'sort' => $o,
            'settings' => $settings,
            'category' => $category,
            'videos' => $videos,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Find category by primary key or by slug
     *
     * @param int|string $identify
     * @return array
     * @throws NotFoundHttpException
     */
    public function findByIdentify($identify): array
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

    /**
     * @return Sort
     */
    protected function buildSort(): Sort
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
     * @param array $category
     * @param FilterForm $filterForm
     * @return VideoQuery
     */
    protected function buildInitialQuery(array $category, FilterForm $filterForm): VideoQuery
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

    /**
     * Показывает список видео роликов текущей категории осортированных по дате добавления.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $id
     * @param string $slug
     * @param int $page
     * @param string $t
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDate(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $id = 0,
        string $slug = '',
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $identify = (0 !== $id) ? $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'date'
        ]);
        $filterForm->load($request->get());
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
     * Показывает список видео роликов текущей категории осортированных по просмортрам.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $id
     * @param string $slug
     * @param int $page
     * @param string $t
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionViews(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $id = 0,
        string $slug = '',
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $identify = (0 !== $id) ? $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'views'
        ]);
        $filterForm->load($request->get());
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
            $response->statusCode = 404;
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
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $id
     * @param string $slug
     * @param int $page
     * @param string $t
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionLikes(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $id = 0,
        string $slug = '',
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $identify = (0 !== $id) ? $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'likes'
        ]);
        $filterForm->load($request->get());
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
     * List videos in category ordered by ctr
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $id
     * @param string $slug
     * @param int $page
     * @param string $t
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCtr(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $id = 0,
        string $slug = '',
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $identify = (0 !== $id) ? $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'ctr'
        ]);
        $filterForm->load($request->get());
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
     * List videos in category ordered by ctr
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $id
     * @param string $slug
     * @param int $page
     * @param string $t
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPopular(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $id = 0,
        string $slug = '',
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $identify = (0 !== $id) ? $id : $slug;
        $category = $this->findByIdentify($identify);

        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'ctr_likes_idx'
        ]);
        $filterForm->load($request->get());
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
                    'ctr_likes_idx' => [ // top rated
                        'asc' => ['vs.ctr_likes_idx' => SORT_DESC],
                        'desc' => ['vs.ctr_likes_idx' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ],
                'defaultOrder' => [
                    'ctr_likes_idx' => [ // top rated
                        'vs.ctr_likes_idx' => SORT_DESC,
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
     * @return string
     */
    public function actionAllCategories(SettingsInterface $settings): string // Переименовать этот метод в AllCategories (template too)
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
}
