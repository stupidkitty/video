<?php

use yii\db\Migration;

/**
 * Class m190206_092629_create_videos
 */
class m190206_092629_create_videos extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        /**
         * Create `videos` table
         */
        $this->createTable('videos', [
            'video_id' => 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'image_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'user_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'slug' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'title' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'description' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'short_description' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'orientation' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\' COMMENT \'1: straight; 2:lesbian; 3:shemale; 4:gay;\'',
            'duration' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'video_url' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'source_url' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'embed' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'on_index' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
            'likes' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 1',
            'dislikes' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'comments_num' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'views' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT 0',
            'max_ctr' => 'double UNSIGNED NOT NULL DEFAULT 0',
            'template' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'status' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
            'published_at' => 'timestamp NULL DEFAULT NULL',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('slug', 'videos', 'slug', true);
        $this->createIndex('user_id', 'videos', 'user_id');
        $this->createIndex('published_at', 'videos', ['published_at', 'status']);
        $this->createIndex('status', 'videos', 'status');
        $this->createIndex('source_url', 'videos', 'source_url');
        $this->createIndex('embed', 'videos', 'embed');
        $this->createIndex('views', 'videos', 'views');
        $this->createIndex('likes', 'videos', 'likes');
        $this->createIndex('max_ctr', 'videos', 'max_ctr');
        $this->execute("ALTER TABLE `videos` ADD FULLTEXT KEY `title` (`title`,`description`,`short_description`)");

        /**
         * Create `videos_categories` table
         */
        $this->createTable('videos_categories', [
            'category_id' => 'smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'position' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 1',
            'slug' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'image' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'meta_title' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'meta_description' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'title' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'h1' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'description' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'seotext' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'param1' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'param2' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'param3' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'videos_num' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'on_index' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 1',
            'enabled' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
            'last_period_clicks' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT 0',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('title', 'videos_categories', 'title');
        $this->createIndex('slug', 'videos_categories', 'slug', true);
        $this->createIndex('position', 'videos_categories', 'position');
        $this->createIndex('last_period_clicks', 'videos_categories', 'last_period_clicks');
        $this->createIndex('enabled', 'videos_categories', 'enabled');

        /**
         * Create `videos_categories_map` table
         */
        $this->createTable('videos_categories_map', [
            'category_id' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'video_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
        ], $tableOptions);

        $this->createIndex('category_id', 'videos_categories_map', 'category_id');
        $this->createIndex('video_id', 'videos_categories_map', 'video_id');

        /**
         * Create `videos_categories_stats` table
         */
        $this->createTable('videos_categories_stats', [
            'category_id' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'date' => 'date NOT NULL',
            'hour' => 'tinyint(2) UNSIGNED NOT NULL DEFAULT 0',
            'clicks' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT 1',
        ], $tableOptions);

        $this->addPrimaryKey('category_id', 'videos_categories_stats', ['category_id', 'date', 'hour']);

        /**
         * Create `videos_images` table
         */
        $this->createTable('videos_images', [
            'image_id' => 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'video_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'position' => 'smallint(3) UNSIGNED NOT NULL DEFAULT 0',
            'filehash' => 'char(32) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'filepath' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'source_url' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'status' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('video_id', 'videos_images', ['video_id', 'position']);
        $this->createIndex('source_url', 'videos_images', 'source_url');
        $this->createIndex('status', 'videos_images', 'status');

        /**
         * Create `videos_import_feeds` table
         */
        $this->createTable('videos_import_feeds', [
            'feed_id' => 'smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'name' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'description' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'delimiter' => 'varchar(16) COLLATE utf8_general_ci NOT NULL DEFAULT \'|\'',
            'enclosure' => 'varchar(16) COLLATE utf8_general_ci NOT NULL DEFAULT \'"\'',
            'fields' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'skip_first_line' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
            'skip_duplicate_urls' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
            'skip_duplicate_embeds' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
            'skip_new_categories' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
            'external_images' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
            'template' => 'varchar(64) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
        ], $tableOptions);

        /**
         * Create `videos_related_map` table
         */
        $this->createTable('videos_related_map', [
            'video_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'related_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
        ], $tableOptions);

        $this->createIndex('video_id', 'videos_related_map', 'video_id');
        $this->createIndex('related_id', 'videos_related_map', 'related_id');

        /**
         * Create `videos_related_map` table
         */
        $this->createTable('videos_stats', [
            'category_id' => 'smallint(5) UNSIGNED NOT NULL',
            'video_id' => 'int(10) UNSIGNED NOT NULL',
            'image_id' => 'int(10) UNSIGNED NOT NULL',
            'best_image' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
            'tested_image' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
            'current_index' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
            'current_shows' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'current_clicks' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'shows0' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'clicks0' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'shows1' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'clicks1' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'shows2' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'clicks2' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'shows3' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'clicks3' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'shows4' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'clicks4' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'total_shows' => 'mediumint(8) UNSIGNED GENERATED ALWAYS AS (`shows0` + `shows1` + `shows2` + `shows3` + `shows4` + 1) VIRTUAL',
            'total_clicks' => 'mediumint(8) UNSIGNED GENERATED ALWAYS AS (`clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4`) VIRTUAL',
            'ctr' => 'double GENERATED ALWAYS AS (`total_clicks` / `total_shows`) VIRTUAL',
        ], $tableOptions);

        $this->addPrimaryKey('video_id', 'videos_stats', ['video_id', 'category_id', 'image_id']);
        $this->createIndex('category_id', 'videos_stats', 'category_id');
        $this->createIndex('image_id', 'videos_stats', ['image_id', 'category_id']);
        $this->createIndex('category_id_2', 'videos_stats', ['category_id', 'best_image', 'tested_image', 'ctr']);
        $this->createIndex('video_id', 'videos_stats', ['video_id', 'image_id', 'ctr']);
        $this->createIndex('category_id_3', 'videos_stats', ['category_id', 'ctr']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190206_092629_video_create cannot be reverted.\n";

        return false;
    }
}
