<?php
namespace SK\VideoModule\Service;

use Yii;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Model\Category as CategoryModel;
use RS\Component\Core\Settings\SettingsInterface;

use SK\VideoModule\Provider\RotateVideoProvider;

class Category
{
    /**
     * Подсчитавыет количество активных видео в категориях
     *
     * @return void
     */
    public function countVideos()
    {
        $sql = "
            UPDATE `videos_categories` as `vc`
            LEFT JOIN (
                SELECT `category_id`, COUNT(*) as `videos_num`
                FROM `videos_categories_map`
                LEFT JOIN `videos` ON `videos`.`video_id` = `videos_categories_map`.`video_id`
                WHERE `videos`.`published_at` < NOW() AND `videos`.`status` = 10
                GROUP BY `category_id`
            ) as `vcm` ON `vc`.`category_id`=`vcm`.`category_id`
            SET `vc`.`videos_num` = IFNULL(`vcm`.`videos_num`, 0)
        ";

        return Yii::$app->db->createCommand($sql)
            ->execute();
    }

    /**
     * SET @total_clicks = (SELECT SUM(`clicks`) FROM `videos_categories_stats` WHERE `date` >= (NOW() - INTERVAL 2 DAY));
     *
     * SELECT `category_id`, (SUM(`clicks`) / @total_clicks) * 100
     * FROM `videos_categories_stats`
     * WHERE `date` >= (NOW() - INTERVAL 2 DAY)
     * GROUP BY `category_id`;
     */
    public function updatePopularity()
    {
        // Для 8-й версии mysql
        /*$sql = "
            UPDATE `videos_categories` as `vc`
            LEFT JOIN (
                SELECT `z`.`category_id`, ROUND(((`z`.`category_clicks` / IFNULL(`z`.`total_clicks`, 1)) * 100), 2) AS `popularity`
                FROM (
                    SELECT DISTINCT `vc2`.`category_id`,
                        SUM(`vcs2`.`clicks`) OVER (PARTITION BY `vc2`.`category_id`) AS `category_clicks`,
                        SUM(`vcs2`.`clicks`) OVER (PARTITION BY `vcs2`.`date`) AS `total_clicks`
                    FROM `videos_categories` AS `vc2`
                    LEFT JOIN `videos_categories_stats` AS `vcs2` USING (`category_id`)
                    WHERE `vcs2`.`date` >= (CURDATE() - INTERVAL 1 DAY) AND `vcs2`.`hour` >= HOUR(CURTIME())
                ) AS `z`
            ) as `x` USING (`category_id`)
            SET `vc`.`popularity` = IFNULL(`x`.`popularity`, 0)
        ";*/

        // До 5.7 версии MySql включительно
        $sql = "
            UPDATE `videos_categories` as `vc`
            LEFT JOIN (
                SELECT `category_id`, SUM(`clicks`) as `clicks_summary`
                FROM `videos_categories_stats`
                WHERE `date` >= (NOW() - INTERVAL 2 DAY)
                GROUP BY `category_id`
            ) as `vcs` ON `vc`.`category_id`=`vcs`.`category_id`
            SET `vc`.`last_period_clicks` = IFNULL(`vcs`.`clicks_summary`, 0)
        ";

        return Yii::$app->db->createCommand($sql)
            ->execute();
    }

    /**
     * Вынести в отдельный класс, переписать логику установки тумбы.
     * Достаточно взять первые пять тумб.
     */
    public function assignCoverImages()
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $categories = CategoryModel::find()
            ->select(['category_id', 'title'])
            ->where(['enabled' => 1])
            ->all();

        if (empty($categories)) {
            return;
        }

        //SELECT `image_id` FROM `videos_stats` WHERE (`category_id`=20) AND (`best_image`=1) AND `image_id` NOT IN (1,2,3) ORDER BY `ctr` LIMIT 1
        $usedImagesIds = [];

        foreach ($categories as $category) {
            // Выбрать тумбы с первой страницы категории
            $dataProvider = new RotateVideoProvider([
                'pagination' => [
                    'defaultPageSize' => (int) $settings->get('items_per_page', 24, 'videos'),
                    'pageSize' => (int) $settings->get('items_per_page', 24, 'videos'),
                    'forcePageParam' => false,
                ],
                'sort' => false,
                'category_id' => $category->getId(),
                'testPerPagePercent' => (int) $settings->get('test_items_percent', 15, 'videos'),
                'testVideosStartPosition' => (int) $settings->get('test_items_start', 3, 'videos'),
            ]);

            $imagesIds = array_column($dataProvider->getModels(), 'image_id');

            if (empty($imagesIds)) {
                continue;
            }

            // Отсеять уже использованные в других категориях (уникальные должны быть)
            $unusedIds = array_diff($imagesIds, $usedImagesIds);

            // Если уникальные иды остались, то выбрать первую и установить ее как обложку категории.
            if (!empty($unusedIds)) {
                $firstId = array_shift($unusedIds);
                $image = Image::findOne(['image_id' => $firstId, 'enabled' => 1]);

                if (null !== $image) {
                    $category->setCoverImage($image);
                }

                // Записать, что данная тумба уже используется.
                $usedImagesIds[] = $image->getId();
            }
        }
    }

    /**
     * Удаляет старую статистику по кликам в категорию.
     *
     * @return void
     */
    public function clearOldStats()
    {
        $db = Yii::$app->get('db');

        // Удаление статы кликов по категориям, которые старше 1 месяца
        $db->createCommand()
            ->delete('videos_categories_stats', '`date` < (NOW() - INTERVAL 1 MONTH)')
            ->execute();
    }
}
