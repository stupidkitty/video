<?php

use yii\db\Migration;

/**
 * Class m210527_074631_create_categories_import_feeds
 */
class m210527_074631_create_categories_import_feeds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            // https://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        /**
         * Create `videos` table
         */
        $this->createTable('videos_categories_import_feeds', [
            'feed_id' => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'name' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'description' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'delimiter' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \',\'',
            'enclosure' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'"\'',
            'fields' => 'json DEFAULT NULL',
            'skip_first_line' => 'tinyint UNSIGNED NOT NULL DEFAULT 1',
            'update_exists' => 'tinyint UNSIGNED NOT NULL DEFAULT 0',
            'activate' => 'tinyint UNSIGNED NOT NULL DEFAULT 0',
            'update_slug' => 'tinyint UNSIGNED NOT NULL DEFAULT 0',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->dropTable('videos_import_feeds');

        $this->execute('
            INSERT INTO `videos_categories_import_feeds`
                   (`feed_id`, `name`, `description`, `delimiter`, `enclosure`, `fields`, `skip_first_line`, `update_exists`, `activate`, `update_slug`)
            VALUES (1, \'main\', \'Основной фид импорта категорий\', \',\', \'"\', \'["category_id", "title", "meta_title", "meta_description", "h1", "description", "seotext"]\', 1, 0, 1, 0);
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210527_074631_create_categories_import_feeds cannot be reverted.\n";

        return false;
    }
}
