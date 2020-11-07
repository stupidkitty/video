<?php


namespace SK\VideoModule\Elastic;

use Elasticsearch\ClientBuilder;


class Elastic
{
    private $primaryKey;

    /**
     * Get elasticsearch client
     * @return \Elasticsearch\Client
     */
    public static function client()
    {
        return ClientBuilder::create()
            ->setHosts(\Yii::$app->params['elasticsearch']['hosts'])
            ->build();
    }

    /**
     * Check Index
     * @return bool
     */
    public static function existsIndex()
    {
        return self::client()->indices()->exists(['index' => self::index()]);
    }

    /**
     * Check Index
     * @return bool
     */
    public static function existsCategoriesIndex()
    {
        return self::client()->indices()->exists(['index' => self::indexCategories()]);
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
     * @return string|null
     */
    public static function index()
    {
        return \Yii::$app->params['elasticsearch']['indexName'];
    }

    /**
     * Index Categories name
     * @return string|null
     */
    public static function indexCategories()
    {
        return \Yii::$app->params['elasticsearch']['indexName'] . '_categories';
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
     * Drop Index
     */
    public static function deleteCategoriesIndex()
    {
        $deleteParams = [
            'index' => self::indexCategories()
        ];
        self::client()->indices()->delete($deleteParams);
    }

    /**
     * Save document to Index
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

    public static function createCategoriesIndex()
    {
        $params = [
            'index' => self::indexCategories(),
            'body' => [
                'mappings' => [
                    '_source' => ['enabled' => true],
                    'properties' => [
                        'title' => ['type' => 'text', 'analyzer' => 'default',],
                    ]
                ],
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            "russian_stop" => [
                                "type" => "stop",
                                "stopwords" => "_russian_"
                            ],
                            'ru_stemmer' => [
                                'type' => 'stemmer',
                                'language' => 'russian'
                            ],
                            'synonym_filter_categories' => [
                                'type' => 'synonym',
                                "lenient" => false,
                                'synonyms_path' => 'synonyms/adult_category_roots.txt',
                            ]
                        ],
                        'analyzer' => [
                            'default' => [
                                'char_filter' => [
                                    'html_strip'
                                ],
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'ru_stemmer',
                                    'synonym_filter_categories',
                                    'russian_stop',
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        ];

        self::client()->indices()->create($params);
    }

    public static function saveCategory($category)
    {
        $params = [
            'index' => self::indexCategories(),
            'id' => $category->category_id,
            'body' => ['title' => $category->title]
        ];

        return self::client()->index($params);
    }

    /**
     * Drop document from Index
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
     * @return Search
     */
    public static function find($searchQuery)
    {
        return new Search($searchQuery);
    }
}