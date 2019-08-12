<?php
namespace SK\VideoModule\Service;

use Yii;
use yii\helpers\ArrayHelper;
use SK\VideoModule\Model\RotationStats;
use SK\VideoModule\Model\VideosRelatedMap;
use SK\VideoModule\Model\Video as VideoModel;
use SK\VideoModule\Model\VideosCategoriesMap;
use SK\VideoModule\Model\Category as CategoryModel;

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
     * SET `v`.`max_ctr` = IFNULL(`vs`.`max_ctr`, 0)
     * WHERE `v`.`max_ctr`!=`vs`.`max_ctr`
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
            SET `v`.`max_ctr` = IFNULL(`vs`.`max_ctr`, 0)
            WHERE `v`.`max_ctr`!=`vs`.`max_ctr`
        ";

        Yii::$app->db
            ->createCommand($sql)
            ->execute();
    }

    /**
     * Обвноляет список категорий у галереи
     */
    public function updateCategoriesByIds(VideoModel $video, array $newCategoriesIds)
    {
        $oldCategoriesIds = ArrayHelper::getColumn($video->categories, 'category_id');

        $removeCategoriesIds = array_diff($oldCategoriesIds, $newCategoriesIds);
        $addCategoriesIds = array_diff($newCategoriesIds, $oldCategoriesIds);

        if (!empty($removeCategoriesIds)) {
            $removeCategories = CategoryModel::find()
                ->where(['category_id' => $removeCategoriesIds])
                ->all();

            foreach ($removeCategories as $removeCategory) {
                $video->removeCategory($removeCategory);
                RotationStats::deleteAll(['video_id' => $video->getId(), 'category_id' => $removeCategory->getId()]);
            }
        }

        if (!empty($addCategoriesIds)) {
            $addCategories = CategoryModel::find()
                ->where(['category_id' => $addCategoriesIds])
                ->all();

            foreach ($addCategories as $addCategory) {
                $video->addCategory($addCategory);

                if ($video->hasPoster()) {
                    RotationStats::addVideo($addCategory, $video, $video->poster, true);
                }
            }
        }
    }


    /**
     * Удаление видео.
     *
     * @param VideoModel $video
     * @return bool
     */
    public function delete(VideoModel $video)
    {
        if ($video->hasImages()) {
            foreach ($video->images as $image) {
                $image->delete();
            }
        }

        if ($video->hasScreenshots()) {
            foreach ($video->screenshots as $screenshot) {
                $screenshot->delete();
            }
        }

        VideosCategoriesMap::deleteAll(['video_id' => $video->getId()]);
        VideosRelatedMap::deleteAll(['video_id' => $video->getId()]);
        VideosRelatedMap::deleteAll(['related_id' => $video->getId()]);
        RotationStats::deleteAll(['video_id' => $video->getId()]);

        return $video->delete();
    }

    /**
     * Пакетное удаление видео по идентификатору.
     *
     * @param integer|array $id
     * @return integer Кол-во удаленных видео.
     */
    public function deleteById($id)
    {
        $deletedNum = 0;
        $query = VideoModel::find()
            ->where(['video_id' => $id]);


        foreach ($query->batch(20) as $videos) {
            foreach ($videos as $video) {
                if ($this->delete($video)) {
                    $deletedNum ++;
                }
            }
        }

        return $deletedNum;
    }
}
