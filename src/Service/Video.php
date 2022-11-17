<?php
namespace SK\VideoModule\Service;

use SK\VideoModule\Model\Category as CategoryModel;
use SK\VideoModule\Model\Video as VideoModel;
use SK\VideoModule\Model\VideosCategories;
use SK\VideoModule\Model\VideosRelatedMap;
use Yii;
use yii\db\Exception;

class Video
{
    /**
     * Обновляет максимальный цтр среди категорий и тумб.
     * ```
     * UPDATE `videos` AS `v`
     * LEFT JOIN (
     *     SELECT `video_id`, `image_id`, MAX(`ctr`) as `max_ctr`
     *     FROM `videos_categories_map`
     *     WHERE `ctr` != 0
     *     GROUP BY `video_id`
     * ) as `vc` ON `v`.`video_id` = `vc`.`video_id`
     * SET `v`.`max_ctr` = IFNULL(`vc`.`max_ctr`, 0)
     * WHERE `v`.`max_ctr`!=`vc`.`max_ctr`
     * ```
     *
     * @return void
     * @throws Exception
     */
    public function updateMaxCtr(): void
    {
        $sql = '
            UPDATE `videos` AS `v`
            LEFT JOIN (
                SELECT `video_id`, MAX(`ctr`) as `max_ctr`
                FROM `videos_categories_map`
                WHERE `category_id` > 0 AND `ctr` != 0
                GROUP BY `video_id`
            ) as `vc` ON `v`.`video_id` = `vc`.`video_id`
            SET `v`.`max_ctr` = IFNULL(`vc`.`max_ctr`, 0)
            WHERE `v`.`max_ctr` != `vc`.`max_ctr`
        ';

        Yii::$app->db
            ->createCommand($sql)
            ->execute();
    }

    /**
     * Обвноляет список категорий у галереи
     */
    public function updateCategoriesByIds(VideoModel $video, array $newCategoriesIds)
    {
        $oldCategoriesIds = \array_column($video->categories, 'category_id');

        $removeCategoriesIds = \array_diff($oldCategoriesIds, $newCategoriesIds);
        $addCategoriesIds = \array_diff($newCategoriesIds, $oldCategoriesIds);

        if (!empty($removeCategoriesIds)) {
            $removeCategories = CategoryModel::find()
                ->where(['category_id' => $removeCategoriesIds])
                ->all();

            foreach ($removeCategories as $removeCategory) {
                $video->removeCategory($removeCategory);
            }
        }

        if (!empty($addCategoriesIds)) {
            $addCategories = CategoryModel::find()
                ->where(['category_id' => $addCategoriesIds])
                ->all();

            foreach ($addCategories as $addCategory) {
                $video->addCategory($addCategory);
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

        VideosCategories::deleteAll(['video_id' => $video->getId()]);
        VideosRelatedMap::deleteAll(['video_id' => $video->getId()]);
        VideosRelatedMap::deleteAll(['related_id' => $video->getId()]);

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
                    $deletedNum++;
                }
            }
        }

        return $deletedNum;
    }
}
