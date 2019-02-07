<?php

use Yii;
use yii\db\Migration;

class m000000_000001_create_videos extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        /*$this->createTable('videos', [
            'video_id' => 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'image_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'user_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'slug' => 'varchar(255) NOT NULL DEFAULT \'\'',
            'title' => 'varchar(255) NOT NULL DEFAULT \'\'',
            'description' => 'text DEFAULT NULL',
            'short_description' => 'varchar(255) NOT NULL DEFAULT \'\'',
            'orientation' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\' COMMENT \'1: straight; 2:lesbian; 3:shemale; 4:gay;\'',
            'duration' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'video_url' => 'varchar(255) NOT NULL DEFAULT \'\'',
            'source_url' => 'varchar(255) NOT NULL DEFAULT \'\'',
            'embed' => 'varchar(255) NOT NULL DEFAULT \'\'',
            'on_index' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
            'likes' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 1',
            'dislikes' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'comments_num' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
            'views' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT 0',
            'template' => 'varchar(255) NOT NULL DEFAULT \'\'',
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
        $this->execute("ALTER TABLE `videos` ADD FULLTEXT KEY `title` (`title`,`description`,`short_description`)");
        $this->execute("ALTER TABLE `videos` ADD FULLTEXT KEY `title_2` (`title`)");*/


        /*$tableName = 'videos_categories';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'category_id' => 'smallint(5) UNSIGNED NOT NULL',
                'position' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 1',
                'slug' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'image' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'meta_title' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'meta_description' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'title' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'h1' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'description' => 'text DEFAULT NULL COMMENT \'other pages description\'',
                'seotext' => 'text DEFAULT NULL',
                'param1' => 'text DEFAULT NULL',
                'param2' => 'text DEFAULT NULL',
                'param3' => 'text DEFAULT NULL',
                'videos_num' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
                'on_index' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
                'shows' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
                'clicks' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT 0',
                'ctr' => 'float NOT NULL DEFAULT 0',
                'reset_clicks_period' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT 20000',
                'on_index' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
                'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ], $tableOptions);

            $this->addPrimaryKey('category_id', $tableName, 'category_id');
            $this->execute("ALTER TABLE `{$tableName}` MODIFY `category_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT");
            $this->createIndex('slug', $tableName, 'slug', true);
            $this->createIndex('position', $tableName, 'position');
        }*/

        /*$tableName = 'videos_categories_map';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'category_id' => 'smallint(5) UNSIGNED NOT NULL',
                'video_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            ], $tableOptions);

            $this->addPrimaryKey('category_id', $tableName, ['category_id', 'video_id']);
            $this->createIndex('video_id', $tableName, 'video_id');
                // add foreign key for table `videos_categories_map`
            $this->addForeignKey(
                'videos_categories_map_ibfk_1',
                $tableName,
                'category_id',
                'videos_categories',
                'category_id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'videos_categories_map_ibfk_2',
                $tableName,
                'video_id',
                'videos',
                'video_id',
                'CASCADE',
                'CASCADE'
            );
        }*/

        /*$tableName = 'videos_images';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'image_id' => 'int(10) UNSIGNED NOT NULL',
                'video_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
                'position' => 'smallint(3) UNSIGNED NOT NULL DEFAULT 0',
                'filehash' => 'char(32) NOT NULL DEFAULT \'\'',
                'filepath' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'source_url' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'status' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
                'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ], $tableOptions);

            $this->addPrimaryKey('image_id', $tableName, 'image_id');
            $this->execute("ALTER TABLE `{$tableName}` MODIFY `image_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT");
            $this->createIndex('video_id', $tableName, ['video_id', 'position']);
            $this->createIndex('source_url', $tableName, 'source_url');
            $this->createIndex('status', $tableName, 'status');
                // add foreign key for table `videos_images`
            $this->addForeignKey(
                'videos_images_ibfk_1',
                $tableName,
                'video_id',
                'videos',
                'video_id',
                null,
                'CASCADE'
            );
        }*/

        /*$tableName = 'videos_import_feeds';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'feed_id' => 'smallint(5) UNSIGNED NOT NULL',
                'name' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'description' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'delimiter' => 'varchar(16) NOT NULL DEFAULT \'|\'',
                'enclosure' => 'varchar(16) NOT NULL DEFAULT \'"\'',
                'fields' => 'text NULL DEFAULT NULL',
                'skip_first_line' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
                'skip_duplicate_urls' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
                'skip_duplicate_embeds' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
                'skip_new_categories' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
                'external_images' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 1',
                'template' => 'varchar(64) NOT NULL DEFAULT \'\'',
            ], $tableOptions);

            $this->addPrimaryKey('feed_id', $tableName, 'feed_id');
            $this->execute("ALTER TABLE `{$tableName}` MODIFY `feed_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT");
        }*/


        /*$tableName = 'videos_related_map';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'video_id' => 'int(10) UNSIGNED NOT NULL',
                'related_id' => 'int(10) UNSIGNED NOT NULL',
            ], $tableOptions);

            $this->addPrimaryKey('video_id', $tableName, ['video_id', 'related_id']);
            $this->createIndex('related_id', $tableName, 'related_id');
                // add foreign key for table `videos_related_map`
            $this->addForeignKey(
                'videos_related_map_ibfk_1',
                $tableName,
                'video_id',
                'videos',
                'video_id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'videos_related_map_ibfk_2',
                $tableName,
                'related_id',
                'videos',
                'video_id',
                'CASCADE',
                'CASCADE'
            );
        }*/

        $tableName = 'videos_stats';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'category_id' => 'smallint(5) UNSIGNED NOT NULL',
                'video_id' => 'int(10) UNSIGNED NOT NULL',
                'image_id' => 'int(10) UNSIGNED NOT NULL',
                'best_image' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
                'tested_image' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
                'published_at' => 'timestamp NULL DEFAULT NULL',
                'duration' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
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
                'total_shows' => 'mediumint(8) UNSIGNED GENERATED ALWAYS AS (`current_shows` + `shows0` + `shows1` + `shows2` + `shows3` + `shows4`) VIRTUAL',
                'total_clicks' => 'mediumint(8) UNSIGNED GENERATED ALWAYS AS (`current_clicks` + `clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4`) VIRTUAL',
                'ctr' => 'double GENERATED ALWAYS AS (`total_clicks` / `total_shows`) VIRTUAL',
            ], $tableOptions);

            $this->addPrimaryKey('video_id', $tableName, ['video_id', 'category_id', 'image_id']);
            $this->createIndex('category_id', $tableName, 'category_id');
            $this->createIndex('image_id', $tableName, 'image_id');
            $this->createIndex('published_at', $tableName, 'published_at');
            $this->createIndex('duration', $tableName, 'duration');
            $this->createIndex('category_id_2', $tableName, ['category_id', 'best_image', 'tested_image', 'ctr']);

            $this->addForeignKey(
                'videos_stats_ibfk_1',
                $tableName,
                'category_id',
                'videos_categories',
                'category_id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'videos_stats_ibfk_2',
                $tableName,
                'video_id',
                'videos',
                'video_id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'videos_stats_ibfk_3',
                $tableName,
                'image_id',
                'videos_images',
                'image_id',
                'CASCADE',
                'CASCADE'
            );
        }

        /**
         * Insert default options in `settings` table
         */
        $tableName = 'settings';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema !== null) {
            $this->batchInsert($tableName, ['section', 'name', 'value'], [
                ['videos', 'items_per_page', '30'],
                ['videos', 'pagination_buttons_count', '7'],
                ['videos', 'recalculate_ctr_period', '2000'],
                ['videos', 'related_enable', '1'],
                ['videos', 'related_number', '12'],
                ['videos', 'related_allow_categories', '1'],
                ['videos', 'related_allow_description', '1'],
                ['videos', 'test_items_percent', '15'],
                ['videos', 'test_items_start', '3'],
                ['videos', 'test_item_period', '200'],
            ]);
        }

        /**
         * Insert cron jobs in `cron_jobs` table
         */
        $tableName = 'cron_jobs';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema !== null) {
            $this->batchInsert($tableName, ['module', 'handler_class', 'cron_expression', 'priority', 'enabled'], [
                ['videos', 'SK\VideoModule\Cron\Job\SwitchTestImageJob', '*/2 * * * *', 1, 1],
                ['videos', 'SK\VideoModule\Cron\Job\ShiftCheckpointJob', '*/2 * * * *', 2, 1],
                ['videos', 'SK\VideoModule\Cron\Job\SetCategoriesThumbsJob', '*/5 * * * *', 3, 1],
            ]);
        }
    }

    public function down()
    {
        echo "m000000_000001_create_videos cannot be reverted.\n";

        return false;
    }
}
