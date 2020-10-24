<?php

namespace SK\VideoModule\Controller;

use SK\VideoModule\Elastic\Search;
use Yii;
use yii\helpers\Url;
use yii\web\Request;
use yii\web\Controller;
use yii\filters\PageCache;
use SK\VideoModule\Model\Video;
use yii\data\ActiveDataProvider;
use yii\base\ViewContextInterface;
use SK\VideoModule\Form\SearchForm;
use RS\Component\Core\Filter\QueryParamsFilter;
use RS\Component\Core\Settings\SettingsInterface;
use yii\web\Response;

/**
 * SearchController implements the search action.
 */
class ElasticController extends SearchController implements ViewContextInterface
{
    /**
     * Lists categorized Videos models.
     *
     * @param int $page
     * @param string $q
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @return mixed
     */
    public function actionIndex(int $page = 1, string $q = '', Request $request, Response $response, SettingsInterface $settings)
    {
        $filterForm = new SearchForm();
        if ($filterForm->load($request->get()) && $filterForm->isValid()) {

            $search = new Search();
            $ids = $search->search($filterForm->getQuery());

            if (!$ids) {
                $response->statusCode = 404;
            } else {
                $query = Video::find()->byIds($ids)->asThumbs();

                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => [
                        'defaultPageSize' => $settings->get('items_per_page', 24, 'videos'),
                        'pageSize' => $settings->get('items_per_page', 24, 'videos'),
                        'forcePageParam' => false,
                        'validatePage' => false,
                    ],
                ]);

                $query->untilNow()
                    ->onlyActive()
                    ->andFilterWhere(['orientation' => $filterForm->orientation])
                    ->asArray();

                $dataProvider->setTotalCount($query->cachedCount());

                $videos = $dataProvider->getModels();
                $totalCount = $dataProvider->getTotalCount();
                $pagination = $dataProvider->getPagination();
            }
        }

        if (empty($videos)) {
            $response->statusCode = 404;
        }

        return $this->render('search', [
            'page' => $page,
            'form' => $filterForm,
            'settings' => $settings,
            'pagination' => $pagination ?? null,
            'videos' => $videos ?? null,
            'totalCount' => $totalCount ?? null,
            'query' => $filterForm->getQuery(),
        ]);
    }
}
