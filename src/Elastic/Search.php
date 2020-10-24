<?php


namespace SK\VideoModule\Elastic;

use Elasticsearch\ClientBuilder;
use SK\VideoModule\Model\VideoInterface;
use yii\elasticsearch\ActiveRecord;


class Search
{
    private $primaryKey;

    public static function client()
    {
        return ClientBuilder::create()->build();
    }

    public static function existsIndex()
    {
        return self::client()->indices()->exists(['index' => self::index()]);
    }

    /**
     * @return array Маппинг этой модели
     * Типы полей: https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html#field-datatypes
     */
    public static function mapping()
    {
        return [
            [
                'categories' => ['type' => 'text', 'boost' => '10'],
                'title' => ['type' => 'text', 'boost' => '3'],
                'description' => ['type' => 'text', 'boost' => '1'],
            ]
        ];
    }

    public static function index()
    {
        return 'jues';
    }

    /**
     * Создание или обновление маппинга модели
     */
    public static function updateMapping()
    {
        $params = [
            'index' => self::index(),
            'body' => [
                'mappings' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => self::mapping()
                ]
            ]
        ];

        self::client()->indices()->create($params);
    }

    /**
     * Создание индекса модели
     */
    public static function createIndex()
    {
        $params = [
            'index' => self::index(),
            'body' => [
                'settings' => [
                    "analysis" => [
                        'filter' => [
                            'russian_stop' => [
                                'type' => 'stop',
                                'stopwords' => '_russian_',
                                'ignore_case' => true,
                            ],
                            'ru_stemmer' => [
                                'type' => 'stemmer',
                                'language' => 'russian'
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
                                    "russian_stop",
                                    "ru_stemmer",
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        self::client()->indices()->create($params);
    }

    /**
     * Удаление индекса модели
     */
    public static function deleteIndex()
    {
        $deleteParams = [
            'index' => self::index()
        ];
        self::client()->indices()->delete($deleteParams);
    }

    public function save()
    {
        $params = [
            'index' => self::index(),
            'body' => $this->attributes
        ];

        if ($this->primaryKey) $params['id'] = $this->primaryKey;

        return self::client()->index($params);
    }

    public function fill($video, $setPrimaryKey = true)
    {
        if ($setPrimaryKey) $this->primaryKey = $video->video_id;

        $categories = [];
        foreach ($video->getCategories()->all() as $category) {
            array_push($categories, $category->title);
        }

        $this->attributes = [
            'categories' => $categories,
            'title' => $video->title,
            'description' => $video->description,
        ];
    }

    public static function deleteDoc($id)
    {
        $params = [
            'index' => self::index(),
            'id' => $id
        ];

        self::client()->delete($params);
    }

    public function search($query)
    {
        $params = [
            'index' => self::index(),
            'body' => [
                'size' => 500,
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['categories', 'title', 'description'],
                        'type' => 'cross_fields',
                    ]]
            ]
        ];
        $res = self::client()->search($params)['hits'];
        if (!$res['total']['value']) return false;

        $ids = [];
        foreach ($res['hits'] as $item) {
            array_push($ids, $item['_id']);
        }
        return $ids;
    }
}