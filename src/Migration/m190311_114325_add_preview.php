<?php

use yii\db\Migration;

/**
 * Class m190311_114325_add_preview
 */
class m190311_114325_add_preview extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `videos` ADD `video_preview` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\' AFTER `duration`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('videos', 'video_preview');
    }
}
