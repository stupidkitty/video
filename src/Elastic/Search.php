<?php


namespace SK\VideoModule\Elastic;


class Search
{
    private $params;

    public function __construct()
    {
        $this->params = [
            'index' => Elastic::index(),
            'body' => \Yii::$app->params['elasticsearch']['search']
        ];
    }

    /**
     * @return array|false
     */
    public function asArrayIds()
    {
        $res = Elastic::client()->search($this->params)['hits'];
        if (!$res['total']['value']) return false;

        $ids = [];
        foreach ($res['hits'] as $item) {
            array_push($ids, $item['_id']);
        }
        return ['ids' => $ids, 'count' => intval($res['total']['value'])];
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
        $this->params['body']['from'] = $this->params['body']['size'] * ($page-1);
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
     * @param string $searchQuery
     * @return Search
     */
    public function setSearchQuery(string $searchQuery): Search
    {
        $this->params['body']['query']['bool']['must']['multi_match']['query'] = $searchQuery;
        return $this;
    }

}