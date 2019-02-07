<?php

use yii\db\Migration;

class m000000_000002_add_categories_stats extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        /*$tableName = 'videos_categories';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema !== null) {
            $this->dropColumn($tableName, 'shows');
            $this->dropColumn($tableName, 'clicks');
            $this->dropColumn($tableName, 'ctr');
            $this->dropColumn($tableName, 'reset_clicks_period');

            $this->addColumn($tableName, 'last_period_clicks', ' MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `enabled`');

            $this->createIndex('last_period_clicks', $tableName, 'last_period_clicks');
        }*/

        /*$tableName = 'videos_categories_stats';
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'category_id' => 'smallint(5) UNSIGNED NOT NULL DEFAULT 0',
                'date' => 'date NOT NULL',
                'hour' => 'tinyint(2) UNSIGNED NOT NULL DEFAULT 0',
                'clicks' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT 1',
            ], $tableOptions);

            $this->addPrimaryKey('category_id', $tableName, ['category_id', 'date', 'hour']);
        }

        $this->insert('cron_jobs', [
            'module' => 'videos',
            'handler_class' => 'SK\VideoModule\Cron\Job\CategoryClicksUpdateJob',
            'cron_expression' => '0 * * * *',
            'priority' => 10,
            'enabled' => 1,
        ]);

        $this->insert('cron_jobs', [
            'module' => 'videos',
            'handler_class' => 'SK\VideoModule\Cron\Job\RecalculateVideosJob',
            'cron_expression' => '01 00 * * *',
            'priority' => 20,
            'enabled' => 1,
        ]);

        $this->insert('cron_jobs', [
            'module' => 'videos',
            'handler_class' => 'SK\VideoModule\Cron\Job\RemoveOldDataJob',
            'cron_expression' => '01 00 * * *',
            'priority' => 30,
            'enabled' => 1,
        ]);*/
        //INSERT INTO `cron_jobs` (`module`, `handler_class`, `cron_expression`, `priority`, `enabled`) VALUES ('videos', 'RS\\Module\\VideoModule\\Cron\\Job\\CategoryClicksUpdateJob', '0 * * * *', 10, 1)
        //INSERT INTO `cron_jobs` (`module`, `handler_class`, `cron_expression`, `priority`, `enabled`) VALUES ('videos', 'RS\\Module\\VideoModule\\Cron\\Job\\RecalculateVideosJob', '01 00 * * *', 20, 1)
        //INSERT INTO `cron_jobs` (`module`, `handler_class`, `cron_expression`, `priority`, `enabled`) VALUES ('videos', 'RS\\Module\\VideoModule\\Cron\\Job\\RemoveOldDataJob', '01 00 * * *', 30, 1)
    }

    public function down()
    {
        echo "m000000_000002_add_categories_stats cannot be reverted.\n";

        return false;
    }
}
