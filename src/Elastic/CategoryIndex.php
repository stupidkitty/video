<?php


namespace SK\VideoModule\Elastic;


class CategoryIndex extends Elastic
{
    /**
     * Check Index
     * @return bool
     */
    public static function existsIndex()
    {
        return self::client()->indices()->exists(['index' => self::index()]);
    }


    /**
     * Get category Index
     * @return string|null
     */
    public static function index()
    {
        return \Yii::$app->params['elasticsearch']['indexName'] . '__categories';
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
     * Create category index
     */
    public static function createIndex()
    {
        $params = [
            'index' => self::index(),
            'body' => [
                'mappings' => [
                    '_source' => ['enabled' => true],
                    'properties' => [
                        'title' => ['type' => 'text', 'analyzer' => 'default',],
                    ]
                ],
                'settings' => \Yii::$app->params['elasticsearch']['categorySettings'],
            ]
        ];

        self::client()->indices()->create($params);
    }


    public static function saveCategory($category)
    {
        $params = [
            'index' => self::index(),
            'id' => $category->category_id,
            'body' => ['title' => $category->title]
        ];

        return self::client()->index($params);
    }


}
