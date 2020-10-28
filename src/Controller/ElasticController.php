<?php

namespace SK\VideoModule\Controller;

use SK\VideoModule\Elastic\Elastic;
use Yii;
use yii\data\Pagination;
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
 * ElasticController implements the search action.
 */
class ElasticController extends SearchController
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
            $pageSize = $settings->get('items_per_page', 24, 'videos');

            $elasticSearchRes = Elastic::find()
                ->setPage($page)
                ->setPageSize($pageSize)
                ->setSearchQuery($filterForm->getQuery())
                ->asArrayIds();

            if (!$elasticSearchRes) {
                $response->statusCode = 404;

            } else {
                $query = Video::find()->byIds($elasticSearchRes['ids'])
                    ->orderByIds($elasticSearchRes['ids'])
                    ->asThumbs()
                    ->andFilterWhere(['orientation' => $filterForm->orientation])
                    ->asArray();

                $videos = $query->all();
                $pagination = new Pagination(['totalCount' => $elasticSearchRes['count'], 'pageSize' => $pageSize]);
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
            'totalCount' => $elasticSearchRes['count'] ?? null,
            'query' => $filterForm->getQuery(),
        ]);
    }
}
