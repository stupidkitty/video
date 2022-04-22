<?php

namespace SK\VideoModule\Controller;

use RS\Component\Core\Filter\QueryParamsFilter;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Cache\PageCache;
use SK\VideoModule\Form\FilterForm;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Query\VideoQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\ViewContextInterface;
use yii\data\ActiveDataProvider;
use yii\di\NotInstantiableException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

/**
 * VideosController implements the CRUD actions for Videos model.
 */
class VideosController extends Controller implements ViewContextInterface
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'queryParams' => [
                'class' => QueryParamsFilter::class,
                'actions' => [
                    'index' => ['page', 'o', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'date' => ['page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'views' => ['page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'likes' => ['page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                    'ctr' => ['page', 't', 'orientation', 'durationMin', 'durationMax', 'isHd', 'source'],
                ],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) $this->get(SettingsInterface::class)->get('enable_page_cache', false),
                //'only' => ['index'],
                'duration' => 600,
                /*'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT MAX(`published_at`) FROM `videos` WHERE `published_at` <= NOW()',
                ],*/
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
     * Lists all Videos models.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $page Текущая страница.
     * @param string $o Сортировка выборки
     * @param string $t Ограничение выборки по времени.
     * @return string
     */
    public function actionIndex(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $page = 1,
        string $o = 'date',
        string $t = 'all-time'
    ): string
    {
        $filterForm = new FilterForm([
            't' => $t,
            'o' => $o,
        ]);
        $filterForm->load($request->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($filterForm);

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
                'sortParam' => 'o',
                'attributes' => [
                    'date' => [ // date
                        'asc' => ['published_at' => SORT_DESC],
                        'desc' => ['published_at' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                    'mv' => [ // most viewed
                        'asc' => ['views' => SORT_DESC],
                        'desc' => ['views' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                    'tr' => [ // top rated
                        'asc' => ['likes' => SORT_DESC],
                        'desc' => ['likes' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                    'ctr' => [ // top rated
                        'asc' => ['max_ctr' => SORT_DESC],
                        'desc' => ['max_ctr' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ],
                'defaultOrder' => [
                    'date' => [
                        'published_at' => SORT_DESC,
                    ],
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($page > 1 && empty($videos)) {
            $response->statusCode = 404;
        }

        return $this->render('all_videos', [
            'page' => $page,
            'sort' => $o,
            'settings' => $settings,
            'pagination' => $pagination,
            'videos' => $videos,
        ]);
    }

    /**
     * Lists all Videos models. Order by date
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $page
     * @param string $t
     * @return mixed
     */
    public function actionDate(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $page = 1,
        string $t = 'all-time'
    )
    {
        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'date'
        ]);
        $filterForm->load($request->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($filterForm);

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

        return $this->render('all_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'pagination' => $pagination,
            'videos' => $videos,
        ]);
    }

    /**
     * Lists all Videos models. Order by views
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $page
     * @param string $t
     * @return string
     */
    public function actionViews(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'views'
        ]);
        $filterForm->load($request->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($filterForm);

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

        if ($page > 1 && empty($videos)) {
            $response->statusCode = 404;
        }

        return $this->render('all_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'pagination' => $pagination,
            'videos' => $videos,
        ]);
    }

    /**
     * Lists all Videos models. Order by likes
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $page
     * @param string $t
     * @return string
     */
    public function actionLikes(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'likes'
        ]);
        $filterForm->load($request->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($filterForm);

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

        return $this->render('all_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'pagination' => $pagination,
            'videos' => $videos,
        ]);
    }

    /**
     * Lists all Videos models. Order by ctr
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $page
     * @param string $t
     * @return string
     */
    public function actionCtr(
        Request $request,
        Response $response,
        SettingsInterface $settings,
        int $page = 1,
        string $t = 'all-time'
    ): string
    {
        $filterForm = new FilterForm([
            't' => $t,
            'o' => 'ctr'
        ]);
        $filterForm->load($request->get());
        $filterForm->isValid();

        $query = $this->buildInitialQuery($filterForm);

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
                    'max_ctr' => SORT_DESC,
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        if ($page > 1 && empty($videos)) {
            $response->statusCode = 404;
        }

        return $this->render('all_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'pagination' => $pagination,
            'videos' => $videos,
        ]);
    }

    /**
     * @param FilterForm $filterForm
     * @return VideoQuery
     */
    protected function buildInitialQuery(FilterForm $filterForm): VideoQuery
    {
        $query = Video::find()
            ->asThumbs()
            ->asArray();

        if ('all-time' !== $filterForm->t) {
            $query->rangedUntilNow($filterForm->t);
        }

        $query
            ->onlyActive()
            ->andFilterWhere(['v.orientation' => $filterForm->orientation])
            ->andFilterWhere(['>=', 'v.duration', $filterForm->durationMin])
            ->andFilterWhere(['<=', 'v.duration', $filterForm->durationMax])
            ->andFilterWhere(['v.is_hd' => $filterForm->isHd]);

        return $query;
    }

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
}
