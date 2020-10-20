<?php


namespace SK\VideoModule\Model;


use yii\elasticsearch\ActiveRecord;

class Search extends ActiveRecord
{
    /**
     * @return array Маппинг этой модели
     */
    public static function mapping()
    {
        return [
            // Типы полей: https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html#field-datatypes
            'properties' => [
                'video_id' => ['type' => 'integer'],
                'image_id' => ['type' => 'integer'],
                'user_id' => ['type' => 'integer'],
                'slug' => ['type' => 'keyword'],

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
                'custom1' => ['type' => 'text'],
                'custom2' => ['type' => 'text'],
                'custom3' => ['type' => 'text'],

                'published_at' => ['type' => 'date'],
                'created_at' => ['type' => 'date'],
                'updated_at' => ['type' => 'date'],
            ]
        ];
    }

    public function attributes()
    {
        return ['video_id', 'image_id', 'user_id', 'slug', 'title', 'description', 'status', 'orientation', 'duration', 'video_preview', 'embed', 'on_index', 'noindex', 'nofollow', 'likes', 'dislikes', 'comments_num', 'is_hd', 'views', 'max_ctr', 'template', 'status', 'custom1', 'custom2', 'custom3', 'published_at', 'created_at', 'updated_at'];
    }

    public static function index()
    {
        return 'jues_net';
    }

    /**
     * Создание или обновление маппинга модели
     */
    public static function updateMapping()
    {
        $db = self::getDb();
        $command = $db->createCommand();
        $command->setMapping(self::index(), self::type(), self::mapping());
    }

    /**
     * Создание индекса модели
     */
    public static function createIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->createIndex(static::index(), [
            //'aliases' => [ /* ... */ ],
            'mappings' => static::mapping(),
            //'settings' => [ /* ... */ ],
        ]);
    }

    /**
     * Удаление индекса модели
     */
    public static function deleteIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->deleteIndex(static::index(), static::type());
    }
}