<?php

use yii\db\Migration;

/**
 * Class m190312_113800_create_crops
 */
class m190312_113800_create_crops extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        /**
         * Create `videos_crops` table
         */
        $this->createTable('videos_crops', [
            'crop_id' => 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'name' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'comment' => 'varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT \'\'',
            'command' => 'text COLLATE utf8_general_ci DEFAULT NULL',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createIndex('name', 'videos_crops', 'name', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('videos_crops');
    }
}
