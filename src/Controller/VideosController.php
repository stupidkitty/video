<?php
namespace SK\VideoModule\Controller;

use Yii;
use yii\web\Request;
use yii\web\Controller;
use yii\filters\PageCache;
use SK\VideoModule\Model\Video;
use yii\data\ActiveDataProvider;
use yii\base\ViewContextInterface;
use yii\web\NotFoundHttpException;
use RS\Component\Core\Filter\QueryParamsFilter;
use RS\Component\Core\Settings\SettingsInterface;

/**
 * VideosController implements the CRUD actions for Videos model.
 */
class VideosController extends Controller implements ViewContextInterface
{
    protected $request;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'queryParams' => [
                'class' => QueryParamsFilter::class,
                'actions' => [
                    'index' => ['page', 'o', 't'],
                    'date' => ['page', 't'],
                    'views' => ['page', 't'],
                    'likes' => ['page', 't'],
                    'ctr' => ['page', 't'],
                ],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) Yii::$container->get(SettingsInterface::class)->get('enable_page_cache', false),
                //'only' => ['index'],
                'duration' => 600,
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT MAX(`published_at`) FROM `videos` WHERE `published_at` <= NOW()',
                ],
                'variations' => [
                    Yii::$app->language,
                    $this->action->id,
                    $this->getRequest()->get('page', 1),
                    $this->getRequest()->get('o', 'date'),
                    $this->getRequest()->get('t', 'all-time'),
                    $this->isMobile(),
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->request = Yii::$container->get(Request::class);

        parent::init();
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
     * @param int $page Текущая страница.
     *
     * @param string $o Сортировка выборки
     *
     * @param string $t Ограничение выборки по времени.
     *
     * @return mixed
     */
    public function actionIndex($page = 1, $o = 'date', $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        $query = Video::find()
            ->asThumbs();

        if ('all-time' === $t) {
            $query->untilNow();
        } elseif ($this->isValidRange($t)) {
            $query->rangedUntilNow($t);
        }

        $query
            ->onlyActive()
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $query->cachedCount(),
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
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
     * @return mixed
     */
    public function actionDate($page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        $query = Video::find()
            ->asThumbs();

        if ('all-time' === $t) {
            $query->untilNow();
        } elseif ($this->isValidRange($t)) {
            $query->rangedUntilNow($t);
        }

        $query
            ->onlyActive()
            ->asArray();

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
     * @return mixed
     */
    public function actionViews($page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        $query = Video::find()
            ->asThumbs();

        if ('all-time' === $t) {
            $query->untilNow();
        } elseif ($this->isValidRange($t)) {
            $query->rangedUntilNow($t);
        }

        $query
            ->onlyActive()
            ->asArray();

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
     * @return mixed
     */
    public function actionLikes($page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        $query = Video::find()
            ->asThumbs();

        if ('all-time' === $t) {
            $query->untilNow();
        } elseif ($this->isValidRange($t)) {
            $query->rangedUntilNow($t);
        }

        $query
            ->onlyActive()
            ->asArray();

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
     * @return mixed
     */
    public function actionCtr($page = 1, $t = 'all-time')
    {
        $page = (int) $page;
        $settings = Yii::$container->get(SettingsInterface::class);

        $query = Video::find()
            ->asThumbs();

        if ('all-time' === $t) {
            $query->untilNow();
        } elseif ($this->isValidRange($t)) {
            $query->rangedUntilNow($t);
        }

        $query
            ->onlyActive()
            ->asArray();

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
                    'max_ctr' => SORT_DESC,
                ]
            ],
        ]);

        $videos = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        return $this->render('all_videos', [
            'page' => $page,
            'sort' => $this->action->id,
            'settings' => $settings,
            'pagination' => $pagination,
            'videos' => $videos,
        ]);
    }

    /**
     * Проверяет корректность параметра $t в экшене контроллера.
     * daily, weekly, monthly, early, all_time
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
        return $this->request;
    }
}
