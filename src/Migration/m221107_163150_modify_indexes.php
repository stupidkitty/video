<?php

use yii\db\Migration;

/**
 * Class m221107_163150_modify_indexes
 */
class m221107_163150_modify_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('
            ALTER TABLE `videos_categories_map` DROP INDEX `total_shows`
        ');

        $this->execute('
            ALTER TABLE `videos_categories_map` ADD INDEX (`is_tested`, `total_shows`)
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221107_163150_modify_indexes cannot be reverted.\n";

        return false;
    }
}
