<?php

use yii\db\Migration;

/**
 * Class m221222_111753_add_search_log
 */
class m221222_111753_add_search_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('
            CREATE TABLE `search_log` (
              `query` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `search_at` date NOT NULL,
              `searches_num` int UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`query`, `search_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221222_111753_add_search_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221222_111753_add_search_log cannot be reverted.\n";

        return false;
    }
    */
}
