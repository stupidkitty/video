<?php

namespace SK\VideoModule\Controller;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Elastic\Elastic;
use SK\VideoModule\Form\SearchForm;
use SK\VideoModule\Model\Video;
use yii\data\Pagination;
use yii\web\Request;
use yii\web\Response;

/**
 * ElasticController implements the search action.
 */
class ElasticController extends SearchController
{
    /**
     * Lists categorized Videos models.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @param int $page
     * @param string $q
     * @return mixed
     */
    public function actionIndex(Request $request, Response $response, SettingsInterface $settings, int $page = 1, string $q = '')
    {
        $filterForm = new SearchForm();

        if ($filterForm->load($request->get()) && $filterForm->isValid()) {
            $pageSize = (int) $settings->get('items_per_page', 24, 'videos');

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
