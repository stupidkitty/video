<?php

use yii\db\Migration;

class m000000_000003_add_indexes extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        /** VIDEOS */
        /*$this->dropIndex('title_2', 'videos');

        $this->addColumn('videos', 'max_ctr', ' DOUBLE UNSIGNED NOT NULL DEFAULT 0 AFTER `views`');

        $this->createIndex('views', 'videos', 'views');
        $this->createIndex('likes', 'videos', 'likes');
        $this->createIndex('max_ctr', 'videos', 'max_ctr');*

        /** CATEGORIES */
        /*$this->createIndex('title', 'videos_categories', 'title');
        $this->createIndex('enabled', 'videos_categories', 'enabled');*/

        /** VIDEOS_STATS */
        $this->dropForeignKey('videos_stats_ibfk_1', 'videos_stats');
        $this->dropForeignKey('videos_stats_ibfk_2', 'videos_stats');
        $this->dropForeignKey('videos_stats_ibfk_3', 'videos_stats');
        $this->dropIndex('image_id', 'videos_stats');
        $this->dropIndex('duration', 'videos_stats');
        $this->dropIndex('published_at', 'videos_stats');

        $this->createIndex('image_id', 'videos_stats', ['image_id', 'category_id']);
        $this->createIndex('video_id', 'videos_stats', ['video_id', 'image_id', 'ctr']);
        $this->createIndex('category_id_3', 'videos_stats', ['category_id', 'ctr']);

        $this->addForeignKey(
            'videos_stats_ibfk_1',
            'videos_stats',
            'category_id',
            'videos_categories',
            'category_id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'videos_stats_ibfk_2',
            'videos_stats',
            'video_id',
            'videos',
            'video_id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'videos_stats_ibfk_3',
            'videos_stats',
            'image_id',
            'videos_images',
            'image_id',
            'CASCADE',
            'CASCADE'
        );

        $this->insert(
            'cron_jobs',
            [
                'module' => 'videos',
                'handler_class' => 'SK\VideoModule\Cron\Job\VideosMaxCtrUpdateJob',
                'cron_expression' => '0 * * * *',
                'priority' => 50,
                'enabled' => 1,
            ]
        );
    }

    public function down()
    {
        echo "m000000_000003_add_indexes cannot be reverted.\n";

        return false;
    }
}
