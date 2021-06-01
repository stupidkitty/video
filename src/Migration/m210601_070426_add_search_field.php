<?php

use yii\db\Migration;

/**
 * Class m210601_070426_add_search_field
 */
class m210601_070426_add_search_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('
            ALTER TABLE `videos` DROP INDEX `title`;
        ');

        $this->execute('
            ALTER TABLE `videos` ADD `search_field` TEXT NULL DEFAULT NULL AFTER `description`, ADD FULLTEXT (`search_field`);
        ');

        $this->execute('
            UPDATE `videos` SET `search_field` = CONCAT_WS(\' \', `title`, `description`);
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210601_070426_add_search_field cannot be reverted.\n";

        return false;
    }
}
