<?php


namespace SK\VideoModule\Elastic;


class VideoIndex extends Elastic
{
    private $primaryKey;
    private $attributes;

    /**
     * Check Index
     *
     * @return bool
     */
    public static function existsIndex()
    {
        return self::client()->indices()->exists(['index' => self::index()]);
    }

    /**
     * @return array mapping Index
     */
    public static function mapping()
    {
        return \Yii::$app->params['elasticsearch']['mapping'];
    }

    /**
     * Index name
     *
     * @return string|null
     */
    public static function index()
    {
        return \Yii::$app->params['elasticsearch']['indexName'];
    }

    /**
     * Create Index, if index exists - drop and create again
     */
    public static function createIndex()
    {
        $params = [
            'index' => self::index(),
            'body' => [

                'settings' => \Yii::$app->params['elasticsearch']['indexSettings'],
                'mappings' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => self::mapping()
                ],

            ]
        ];

        self::client()->indices()->create($params);
    }

    /**
     * Drop Index
     */
    public static function deleteIndex()
    {
        $deleteParams = [
            'index' => self::index()
        ];
        self::client()->indices()->delete($deleteParams);
    }

    /**
     * Save document to Index
     *
     * @return array|callable
     */
    public function save()
    {
        $params = [
            'index' => self::index(),
            'body' => $this->attributes
        ];

        if ($this->primaryKey) $params['id'] = $this->primaryKey;

        return self::client()->index($params);
    }



    /**
     * Fill model attributes
     *
     * @param $video
     * @param bool $setPrimaryKey
     */
    public function fill($video, $setPrimaryKey = true)
    {
        if ($setPrimaryKey) $this->primaryKey = $video->video_id;

        $categories = [];
        foreach ($video->getCategories()->all() as $category) {
            array_push($categories, $category->category_id);
        }

        $this->attributes = [
            'category_ids' => $categories,
            'description' => $video->description . ' ' . $video->title,
            'published_at' => $video->published_at
        ];
    }

    /**
     * Drop document from Index
     *
     * @param $id
     */
    public static function deleteDoc($id)
    {
        $params = [
            'index' => self::index(),
            'id' => $id
        ];

        self::client()->delete($params);
    }

    /**
     * Search query
     *
     * @param $searchQuery
     * @return Search
     */
    public static function find($searchQuery)
    {
        return new Search($searchQuery);
    }
}
