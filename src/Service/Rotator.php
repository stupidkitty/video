<?php
namespace SK\VideoModule\Service;

use Yii;
use yii\db\Expression;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\RotationStats;
use RS\Component\Core\Settings\SettingsInterface;

class Rotator
{
    /**
     * @var integer Default test item period (test shows).
     */
    const TEST_ITEM_PERIOD = 200;

    /**
     * @var int default recalculate ctr period (shows);
     */
    const RECALCULATE_CTR_PERIOD = 2000;

     /**
     * @var int default thumbs per page;
     */
    const ITEMS_PER_PAGE = 24;

    /**
     * @var int default thumbs per page;
     */
    const TEST_PERCENT = 15;

    /**
     * Устанавливает флаг "тестировано" у записи
     *
     * @return void
     */
    public function markAsTestedRows()
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $test_item_period = (int) $settings->get('test_item_period', static::TEST_ITEM_PERIOD, 'videos');

            // Завершим тестовый период у тумб, если набралась необходимая статистика.
        $query = RotationStats::find()
            ->select(['category_id', 'video_id', 'image_id', 'tested_image', 'tested_at'])
            ->where(['tested_image' => 0])
            ->andWhere(['>=', 'total_shows', $test_item_period]);

        foreach ($query->batch(50) as $rows) {
            foreach ($rows as $row) {
                $row->tested_image = 1;
                $row->tested_at = gmdate('Y-m-d H:i:s');
                $row->save();
            }
        }

        /**
         * Для нескольких тумб: выбрать все видео. Затем проверить есть ли у текущего фото еще не закончившие тест.
         * Если все тумбы у видео закончили тест, то проверим, если ли у видео другие тумбы. Если есть, то начнем тестировать их.
         * Для этого снимем флажок "лучшая тумба" и переведем его на новую.
         * После того, как закончатся все тумбы (проверим, если нетестированные еще) Выберем лучшую тумбу из всех имеющихся по цтр
         * и выставим у нее флажок "лучшая тумба".
         */
    }

    /**
     * Метод смещает контрольные точки у тумб. Всего контрольных точек пять.
     * Значит берем клики периода рекалькуляции цтр и раскидываем равномерно по пяти точкам.
     * Затем выберем все тумбы, которые достигли необходимого значения, обнулим счетчик и сместим вправо на следующую точку.
     *
     * @return void
     */
    public function shiftHistoryCheckpoint()
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $recalculate_ctr_period = $settings->get('recalculate_ctr_period', static::RECALCULATE_CTR_PERIOD, 'videos');
        $showsCheckpointValue = (int) ceil($recalculate_ctr_period / 5);

        $thumbStats = RotationStats::find()
            ->select(['video_id', 'category_id', 'image_id', 'current_shows', 'current_clicks', 'current_index'])
            ->where(['>=', 'current_shows', $showsCheckpointValue])
            ->asArray()
            ->all();

        if (empty($thumbStats)) {
            return;
        }

        foreach ($thumbStats as $thumbStat) {
            $currentIndex = (int) $thumbStat['current_index'];

            if ($currentIndex == 4) {
                $currentIndex = 0;
            } else {
                $currentIndex ++;
            }

            RotationStats::updateAll(
                [
                    'current_shows' => 0,
                    'current_clicks' => 0,
                    'current_index' => $currentIndex,
                    "shows{$currentIndex}" => (int) $thumbStat['current_shows'],
                    "clicks{$currentIndex}" => (int) $thumbStat['current_clicks'],
                ],
                [
                    'video_id' => $thumbStat['video_id'],
                    'category_id' => $thumbStat['category_id'],
                    'image_id' => $thumbStat['image_id'],
                ]
            );
        }
    }

    /**
     * Сбрасывает ротацию старых видео, если в категории больше не осталось что ротировать.
     * Пропускает топовые видео с 1-й страницы.
     *
     * @return void
     */
    public function resetOldTestedVideos()
    {
        $db = Yii::$app->db;
        $settings = Yii::$container->get(SettingsInterface::class);

        $thumbsPerPage = (int) $settings->get('items_per_page', static::ITEMS_PER_PAGE, 'videos');
        $testThumbsPercent = (int) $settings->get('test_items_percent', static::TEST_PERCENT, 'videos');
        $testPerPage = (int) ceil(($thumbsPerPage / 100) * $testThumbsPercent);
        $untouchablesThumbsNum = $thumbsPerPage - $testPerPage;

        $sql = "SELECT `category_id`, COUNT(*) - SUM(`tested_image`) as `tested_diff`
                FROM `videos_stats` as `vs`
                LEFT JOIN `videos` as `v` ON (`vs`.`video_id`=`v`.`video_id`)
                WHERE `v`.`published_at`<= NOW() AND `v`.`status` = 10
                GROUP BY `category_id`
                HAVING `tested_diff` < :testSpotsNum"; // = 0

        $categories = $db->createCommand($sql)
            ->bindValue(':testSpotsNum', $testPerPage)
            ->queryAll();

        foreach ($categories as $category) {
            $resetLimit = $testPerPage;//($testPerPage - (int) $category['tested_diff']) * 2;

            // найдем топовые тумбы в этой категории.
            $untouchablesThumbs = RotationStats::find()
                ->alias('rs')
                ->select(['rs.video_id'])
                ->leftJoin(['v' => Video::tableName()], 'rs.video_id = v.video_id')
                ->where(['rs.category_id' => $category['category_id']])
                ->andWhere(['rs.best_image' => 1])
                ->andWhere(['rs.tested_image' => 1])
                ->andWhere(['>', 'rs.ctr', 0])
                ->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
                ->andWhere(['v.status' => 10])
                ->orderBy(['rs.ctr' => SORT_DESC])
                ->limit($untouchablesThumbsNum)
                ->column();

            // найдем старые тумбы в категории. при этом исключим топовые (их ротировать нельзя).
            $resetThumbs = RotationStats::find()
                ->alias('rs')
                ->select(['rs.video_id'])
                ->leftJoin(['v' => Video::tableName()], 'rs.video_id = v.video_id')
                ->where(['rs.category_id' => $category['category_id']])
                ->andWhere(['rs.best_image' => 1])
                ->andWhere(['rs.tested_image' => 1])
                ->andWhere(['>=', 'rs.ctr', 0])
                ->andFilterWhere(['NOT IN', 'rs.video_id', $untouchablesThumbs])
                ->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
                ->andWhere(['v.status' => 10])
                ->orderBy(['rs.tested_at' => SORT_DESC])
                ->limit($resetLimit)
                ->column();

            RotationStats::updateAll([
                'tested_image' => 0,
                'tested_at' => null,
                'current_index' => 0,
                'current_shows' => 0,
                'current_clicks' => 0,
                'shows0' => 0,
                'clicks0' => 0,
                'shows1' => 0,
                'clicks1' => 0,
                'shows2' => 0,
                'clicks2' => 0,
                'shows3' => 0,
                'clicks3' => 0,
                'shows4' => 0,
                'clicks4' => 0,
            ], [
                'video_id' => $resetThumbs,
                'category_id' => $category['category_id'],
            ]);
        }
    }
}
