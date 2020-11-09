<?php


namespace SK\VideoModule\Elastic;


class Search extends Elastic
{
    private $params;
    private $searchQuery;

    public function __construct($searchQuery)
    {
        $this->searchQuery = $searchQuery;
        $this->params = [
            'index' => VideoIndex::index(),
            'body' => \Yii::$app->params['elasticsearch']['search']
        ];
        $this->params['body']['query']['bool']['should']['match']['description'] = $searchQuery;
        $this->setCategoryQuery();
    }

    /**
     * Return query result like map ids => array(id), count => int
     *
     * @return array|false
     */
    public function asArrayIds()
    {
        if (empty($this->params['body']['query']['bool']['must'])) {
            $this->params['body']['query'] = ["match" => ["description" => $this->searchQuery]];
        }

        $res = Elastic::client()->search($this->params)['hits'];

        if (isset($this->params['body']['query']['bool']['must'])) {
            while ($res['total']['value'] < 60) {
                array_pop($this->params['body']['query']['bool']['must']);
                $res = Elastic::client()->search($this->params)['hits'];
            }
        }

        if (!$res['total']['value']) return false;

        $ids = [];
        foreach ($res['hits'] as $item) {
            array_push($ids, $item['_id']);
        }

        $count = intval($res['total']['value']);
        return ['ids' => $ids, 'count' => $count];
    }

    /**
     * @return array|false
     */
    public function asArray()
    {
        $res = Elastic::client()->search($this->params)['hits'];
        if (!$res['total']['value']) return false;

        return $res['hits'];
    }

    /**
     * @param int $page
     * @return Search
     */
    public function setPage(int $page)
    {
        $this->params['body']['from'] = $this->params['body']['size'] * ($page - 1);
        return $this;
    }

    /**
     * @param int $pageSize
     * @return Search
     */
    public function setPageSize(int $pageSize)
    {
        $this->params['body']['size'] = $pageSize;
        return $this;
    }

    /**
     * @param string $gte
     * @param string $lte
     * @return Search
     */
    public function setDateRange(string $gte, string $lte = 'now/d')
    {
        $this->params['body']['query']['bool']['filter']['range']['published_at']['lte'] = $lte;
        $this->params['body']['query']['bool']['filter']['range']['published_at']['gte'] = $gte;
        return $this;
    }

    /**
     * @param array $fields
     * @return Search
     */
    public function setFields(array $fields)
    {
        $this->params['body']['query']['bool']['must']['multi_match']['fields'] = $fields;
        return $this;
    }

    /**
     * Set to elastic query categories filter
     */
    public function setCategoryQuery()
    {
        foreach ($this->searchCategories() as $category) {
            array_push($this->params['body']['query']['bool']['must'],
                ['term' => ['category_ids' => $category['_id']]]);

        }
    }

    /**
     * Search categories for current elastic query
     *
     * @return mixed
     */
    private function searchCategories()
    {
        $params = [
            'index' => CategoryIndex::index(),
            'body' => [
                "query" => [
                    "match" => [
                        "title" => [
                            "query" => $this->searchQuery,
                            "fuzziness" => "auto"
                        ]
                    ]
                ]
            ]
        ];

        return self::client()->search($params)['hits']['hits'];
    }

}
