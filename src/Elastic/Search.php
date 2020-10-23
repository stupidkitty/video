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

<<<<<<< HEAD
    public static function existsIndex()
    {
        return self::client()->indices()->exists(['index' => self::index()]);
    }

=======
>>>>>>> 6df0e73... elastic start
    /**
     * @return array Маппинг этой модели
     * Типы полей: https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html#field-datatypes
     */
    public static function mapping()
    {
        return [
<<<<<<< HEAD
            [
                'categories' => ['type' => 'keyword', 'boost' => '10'],
                'title' => ['type' => 'text', 'boost' => '3'],
                'description' => ['type' => 'text', 'boost' => '1'],
            ]
=======
            'video_id' => ['type' => 'integer'],
            'image_id' => ['type' => 'integer'],
            'user_id' => ['type' => 'integer'],
            'slug' => ['type' => 'keyword'],
            'categories' => ['type' => 'keyword', 'boost' => 2],

            'title' => ['type' => 'text'],
            'description' => ['type' => 'text'],
            'orientation' => ['type' => 'byte'],
            'duration' => ['type' => 'integer'],

            'video_preview' => ['type' => 'keyword'],
            'embed' => ['type' => 'keyword'],
            'on_index' => ['type' => 'boolean'],
            'noindex' => ['type' => 'boolean'],

            'nofollow' => ['type' => 'boolean'],
            'likes' => ['type' => 'integer'],
            'dislikes' => ['type' => 'integer'],
            'comments_num' => ['type' => 'integer'],

            'is_hd' => ['type' => 'boolean'],
            'views' => ['type' => 'integer'],
            'max_ctr' => ['type' => 'double'],
            'template' => ['type' => 'keyword'],

            'status' => ['type' => 'byte'],
            'published_at' => ['type' => 'date'],
            'created_at' => ['type' => 'date'],
            'updated_at' => ['type' => 'date'],
>>>>>>> 6df0e73... elastic start
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
<<<<<<< HEAD
            'index' => self::index(),
=======
            'index' => 'my_index',
>>>>>>> 6df0e73... elastic start
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
<<<<<<< HEAD
                        'filter' => [
                            'stopwords_ru' => [
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
                                    "russian_stemmer",
=======
                        "filter" => [
                            "ru_stop" => [
                                "type" => "stop",
                                "stopwords" => "_russian_"
                            ],
                            "ru_stemmer" => [
                                "type" => "stemmer",
                                "language" => "russian"
                            ]
                        ],
                        "analyzer" => [
                            "default" => [
                                "char_filter" => [
                                    "html_strip"
                                ],
                                "tokenizer" => "standard",
                                "filter" => [
                                    "lowercase",
                                    "ru_stop",
                                    "ru_stemmer"
>>>>>>> 6df0e73... elastic start
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

<<<<<<< HEAD
        $categories = [];
        foreach ($video->getCategories()->all() as $category) {
            array_push($categories, $category->title);
        }

        $this->attributes = [
            'categories' => $categories,
            'title' => $video->title,
            'description' => $video->description,
=======
        $this->attributes = [
            'video_id' => $video->video_id,
            'image_id' => $video->image_id,
            'user_id' => $video->user_id,
            'slug' => $video->slug,
            'status' => $video->status,
            'categories' => $video->getCategories(),

            'title' => $video->title,
            'description' => $video->description,
            'orientation' => $video->orientation,
            'duration' => $video->duration,

            'video_preview' => $video->video_preview,
            'embed' => $video->embed,
            'on_index' => $video->on_index,
            'noindex' => $video->noindex,

            'nofollow' => $video->nofollow,
            'likes' => $video->likes,
            'dislikes' => $video->dislikes,
            'comments_num' => $video->comments_num,

            'is_hd' => $video->is_hd,
            'views' => $video->views,
            'max_ctr' => $video->max_ctr,
            'template' => $video->template,

            'custom1' => $video->custom1,
            'custom2' => $video->custom2,
            'custom3' => $video->custom3,

            'published_at' => $video->published_at,
            'created_at' => $video->created_at,
            'updated_at' => $video->updated_at,
>>>>>>> 6df0e73... elastic start
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
<<<<<<< HEAD
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
=======
                'size' => 50,
                "query" => [
                    'bool' => [
                        'must' => [
                        'multi_match' => [
                            'query' => $query,
                            "fields" => [
                                'description', 'title',
                            ],
                            'type' => 'best_fields',
                        ]],
                        'must' => [

                        ]
                    ]
                ]
            ]
        ];
        return self::client()->search($params)['hits']['hits'];
>>>>>>> 6df0e73... elastic start
    }
}