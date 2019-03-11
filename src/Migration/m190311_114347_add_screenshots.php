<?php

use yii\db\Migration;

/**
 * Class m190311_114347_add_screenshots
 */
class m190311_114347_add_screenshots extends Migration
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
         * Create `videos_screenshots` table
         */
        $this->createTable('videos_screenshots', [
            'screenshot_id' => 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'video_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'path' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'source_url' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('video_id', 'videos_screenshots', 'video_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('videos_screenshots');
    }
}
