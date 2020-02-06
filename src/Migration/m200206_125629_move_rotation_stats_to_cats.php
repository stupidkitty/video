<?php

use yii\db\Migration;

/**
 * Class m200206_125629_move_rotation_stats_to_cats
 */
class m200206_125629_move_rotation_stats_to_cats extends Migration
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

        $sql = "ALTER TABLE `videos_categories_map`
                    ADD `is_tested` TINYINT UNSIGNED NULL DEFAULT 0 AFTER `video_id`,
                    ADD `tested_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_tested`,
                    ADD `current_index` INT UNSIGNED NULL DEFAULT 0 AFTER `tested_at`,
                    ADD `current_shows` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `current_index`,
                    ADD `current_clicks` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `current_shows`,
                    ADD `shows0` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `current_clicks`,
                    ADD `clicks0` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows0`,
                    ADD `shows1` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks0`,
                    ADD `clicks1` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows1`,
                    ADD `shows2` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks1`,
                    ADD `clicks2` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows2`,
                    ADD `shows3` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks2`,
                    ADD `clicks3` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows3`,
                    ADD `shows4` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks3`,
                    ADD `clicks4` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows4`,
                    ADD `shows5` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks4`,
                    ADD `clicks5` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows5`,
                    ADD `shows6` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks5`,
                    ADD `clicks6` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows6`,
                    ADD `shows7` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks6`,
                    ADD `clicks7` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows7`,
                    ADD `shows8` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks7`,
                    ADD `clicks8` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows8`,
                    ADD `shows9` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `clicks8`,
                    ADD `clicks9` SMALLINT UNSIGNED NULL DEFAULT 0 AFTER `shows9`,
                    ADD `total_shows` MEDIUMINT UNSIGNED AS (`shows0` + `shows1` + `shows2` + `shows3` + `shows4` + `shows5` + `shows6` + `shows7` + `shows8` + `shows9`) STORED AFTER `clicks9`,
                    ADD `total_clicks` MEDIUMINT UNSIGNED AS (`clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4` + `clicks5` + `clicks6` + `clicks7` + `clicks8` + `clicks9`) STORED AFTER `total_shows`,
                    ADD `ctr` DECIMAL(6,6) UNSIGNED AS (`total_clicks` / GREATEST(`total_shows`,1)) STORED AFTER `total_clicks`";
        $this->execute($sql);

        $sql = "ALTER TABLE `videos_categories_map` DROP INDEX `category_id`";
        $this->execute($sql);

        $sql = "ALTER TABLE `videos_categories_map` DROP INDEX `video_id`";
        $this->execute($sql);

        $sql = "ALTER TABLE `videos_categories_map` ADD PRIMARY KEY (`category_id`, `video_id`)";
        $this->execute($sql);

        $sql = "ALTER TABLE `videos_categories_map` ADD INDEX (`category_id`, `is_tested`, `ctr`)";
        $this->execute($sql);

        $sql = "ALTER TABLE `videos_categories_map` ADD INDEX (`category_id`, `ctr`)";
        $this->execute($sql);

        $sql = "ALTER TABLE `videos_categories_map` ADD INDEX (`current_shows`)";
        $this->execute($sql);

        $sql = "ALTER TABLE `videos_categories_map` ADD INDEX (`total_shows`)";
        $this->execute($sql);
    }
    /* UPDATE `videos_categories_map` as `vcm`
LEFT JOIN `videos_stats` as `vc` ON (`vcm`.`category_id`=`vc`.`category_id` AND `vcm`.`video_id`=`vc`.`video_id`)
SET `vcm`.`is_tested`=`vc`.`tested_image`, `vcm`.`tested_at`=`vc`.`tested_at`, `vcm`.`current_index`=`vc`.`current_index`, `vcm`.`current_shows`=`vc`.`current_shows`, `vcm`.`current_clicks`=`vc`.`current_clicks`, `vcm`.`shows0`=`vc`.`shows0`, `vcm`.`clicks0`=`vc`.`clicks0`, `vcm`.`shows1`=`vc`.`shows1`, `vcm`.`clicks1`=`vc`.`clicks1`, `vcm`.`shows2`=`vc`.`shows2`, `vcm`.`clicks2`=`vc`.`clicks2`, `vcm`.`shows3`=`vc`.`shows3`, `vcm`.`clicks3`=`vc`.`clicks3`, `vcm`.`shows4`=`vc`.`shows4`, `vcm`.`clicks4`=`vc`.`clicks4`*/
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200206_125629_move_rotation_stats_to_cats cannot be reverted.\n";

        return false;
    }
}
