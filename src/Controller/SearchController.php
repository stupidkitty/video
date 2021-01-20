<?php

namespace SK\VideoModule\Controller;

use RS\Component\Core\Filter\QueryParamsFilter;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Form\SearchForm;
use SK\VideoModule\Model\Video;
use Yii;
use yii\base\ViewContextInterface;
use yii\data\ActiveDataProvider;
use yii\filters\PageCache;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

/**
 * SearchController implements the search action.
 */
class SearchController extends Controller implements ViewContextInterface
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
                    'index' => ['q', 'page', 'orientation'],
                ],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) $this->get(SettingsInterface::class)->get('enable_page_cache', false),
                //'only' => ['index'],
                'duration' => 3600,
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT 1',
                ],
                'variations' => [
                    Yii::$app->language,
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
     * Search action.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $page
     * @return mixed
     */
    public function actionIndex(Request $request, Response $response, SettingsInterface $settings, int $page = 1)
    {
        // задрочка для чпу, форма доложна быть методом POST --begin
        /*if ($request->isPost && '' !== $request->post('q', '')) {
            $request->setQueryParams(['q' => $request->post('q', ''), 'page' => $page]);
            $request->resolve();
            $this->redirect(Url::toRoute(['search/index', 'q' =>  $request->post('q')]), 301);
        }
        if ('' !== $q) {
            $request->setQueryParams(['q' => $q, 'page' => $page]);
        }*/
        // задрочка для чпу --end

        $query = Video::find()
            ->asThumbs();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                'forcePageParam' => false,
                'validatePage' => false,
            ],
        ]);

        $filterForm = new SearchForm();

        if ($filterForm->load($request->get()) && $filterForm->isValid()) {
            $query
                ->select('*, MATCH (`title`, `description`) AGAINST (:query) AS `relevance`')
                ->where('MATCH (`title`, `description`) AGAINST (:query)', [
                    ':query' => $filterForm->getQuery(),
                ])
                ->untilNow()
                ->onlyActive()
                ->andFilterWhere(['orientation' => $filterForm->orientation])
                ->orderBy(['relevance' => SORT_DESC])
                ->asArray();

            $dataProvider->setTotalCount($query->cachedCount());
        } else {
            $query->where('1=0');
        }

        $videos = $dataProvider->getModels();
        $totalCount = $dataProvider->getTotalCount();
        $pagination = $dataProvider->getPagination();

        if (empty($videos)) {
            $response->statusCode = 404;
        }

        return $this->render('search', [
            'page' => $page,
            'form' => $filterForm,
            'settings' => $settings,
            'pagination' => $pagination,
            'videos' => $videos,
            'totalCount' => $totalCount,
            'query' => $filterForm->getQuery(),
        ]);
    }

    /**
     * Detect user is mobile device
     *
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function isMobile(): bool
    {
        $deviceDetect = $this->get('device.detect');

        return $deviceDetect->isMobile() || $deviceDetect->isTablet();
    }

    /**
     * Get instance by tag name form DI container
     *
     * @param $name
     * @return object
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function get(string $name)
    {
        return Yii::$container->get($name);
    }
}
