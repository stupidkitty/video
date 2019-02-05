<?php
namespace SK\VideoModule\Service;

use Yii;

class Video
{
    /**
     * Обновляет максимальный цтр среди категорий и тумб.
     *
     * ```
     * UPDATE `videos` AS `v`
     * LEFT JOIN (
     *     SELECT `video_id`, `image_id`, MAX(`ctr`) as `max_ctr`
     *     FROM `videos_stats`
     *     WHERE `ctr` != 0
     *     GROUP BY `video_id`
     * ) as `vs` ON `v`.`video_id` = `vs`.`video_id`
     * SET `v`.`max_ctr`=IFNULL(`vs`.`max_ctr`, 0)
     * WHERE `v`.`published_at` <= NOW() AND `v`.`status` = 10 AND `v`.`max_ctr`!=`vs`.`max_ctr`
     * ```
     * 
     * @return void
     */
    public function updateMaxCtr()
    {
        $sql = "
            UPDATE `videos` AS `v`
            LEFT JOIN (
                SELECT `video_id`, MAX(`ctr`) as `max_ctr`
                FROM `videos_stats`
                WHERE `ctr` != 0
                GROUP BY `video_id`
            ) as `vs` ON `v`.`video_id` = `vs`.`video_id`
            SET `v`.`max_ctr`=IFNULL(`vs`.`max_ctr`, 0)
            WHERE `v`.`published_at` <= NOW() AND `v`.`status` = 10 AND `v`.`max_ctr`!=`vs`.`max_ctr`
        ";

        Yii::$app->db
            ->createCommand($sql)
            ->execute();
    }
}