<?php

use yii\db\Migration;

class m000000_000005_vs_virtual extends Migration
{
    public function safeUp()
    {
        /** VIDEOS_STATS */
        $this->execute('ALTER TABLE `videos_stats` CHANGE `total_clicks` `total_clicks` MEDIUMINT(8) UNSIGNED AS (`clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4`) VIRTUAL');
        $this->execute('ALTER TABLE `videos_stats` CHANGE `total_shows` `total_shows` MEDIUMINT(8) UNSIGNED AS (`shows0` + `shows1` + `shows2` + `shows3` + `shows4` + 1) VIRTUAL');
    }

    public function safeDown()
    {
        /** VIDEOS_STATS */
        $this->execute('ALTER TABLE `videos_stats` CHANGE `total_clicks` `total_clicks` MEDIUMINT(8) UNSIGNED AS (`current_clicks` + `clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4`) VIRTUAL');
        $this->execute('ALTER TABLE `videos_stats` CHANGE `total_shows` `total_shows` MEDIUMINT(8) UNSIGNED AS (`current_shows` + `shows0` + `shows1` + `shows2` + `shows3` + `shows4`) VIRTUAL');


        return true;
    }
}
