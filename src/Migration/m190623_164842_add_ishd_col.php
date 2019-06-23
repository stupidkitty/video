<?php

use yii\db\Migration;

/**
 * Class m190623_164842_add_ishd_col
 */
class m190623_164842_add_ishd_col extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `videos` ADD `is_hd` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `comments_num`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('videos', 'is_hd');
    }
}
