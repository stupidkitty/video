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
            'categories' => ['type' => 'text', 'boost' => '10'],
            'title' => ['type' => 'text', 'boost' => '3'],
            'description' => ['type' => 'text', 'boost' => '1'],
            'published_at' => ['type' => 'date', 'format' => 'strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss'],
        ];
    }


    public static function index()
    {
        return static::getDsnAttribute('dbname', \Yii::$app->getDb()->dsn);
    }

    private static function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

    /**
     * Создание индекса модели
     */
    public static function createIndex()
    {
        $params = [
            'index' => self::index(),
            'body' => [
                'mappings' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => self::mapping()
                ],
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
            'published_at' => $video->published_at
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

    public static function search($query, $page = 1, $pageSize = 24)
    {
        $params = [
            'index' => self::index(),
            'body' => [
                'size' => $pageSize,
                'from' => ($page - 1) * $pageSize,
                'query' => [
                    'bool' => [
                        'must' => [
                            'range' => [
                                'published_at' => [
                                    "lt" => 'now/d'
                                ]
                            ]
                        ],
                        'must' => [
                            'multi_match' => [
                                'query' => $query,
                                'fields' => ['categories', 'title', 'description'],
                                'type' => 'cross_fields',
                            ]
                        ],
                    ]
                ]
            ]
        ];
        $res = self::client()->search($params)['hits'];
        if (!$res['total']['value']) return false;

        $ids = [];
        foreach ($res['hits'] as $item) {
            array_push($ids, $item['_id']);
        }
        return ['ids' => $ids, 'count' => intval($res['total']['value'])];
    }
}