<?php

use yii\db\Migration;

/**
 * Class m191124_093920_add_noindex
 */
class m191124_093920_add_noindex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `videos` ADD `noindex` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `on_index`,
                        ADD `nofollow` TINYINT UNSIGNED NOT NULL DEFAULT 0
                        AFTER `noindex`');

        $this->execute('ALTER TABLE `videos_stats` ADD `tested_at` TIMESTAMP NULL DEFAULT NULL AFTER `tested_image`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('videos', 'noindex');
        $this->dropColumn('videos', 'nofollow');
        $this->dropColumn('videos_stats', 'tested_at');
    }
}
