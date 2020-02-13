<?php
namespace SK\VideoModule\Service;

use Yii;
use yii\db\Expression;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosCategories;
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
    public function markAsTestedRows(): void
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $test_item_period = (int) $settings->get('test_item_period', static::TEST_ITEM_PERIOD, 'videos');

            // Завершим тестовый период у тумб, если набралась необходимая статистика.
        $query = VideosCategories::find()
            ->select(['category_id', 'video_id', 'is_tested', 'tested_at'])
            ->where(['is_tested' => 0])
            ->andWhere(['>=', 'total_shows', $test_item_period]);

        foreach ($query->batch(50) as $rows) {
            foreach ($rows as $row) {
                $row->is_tested = 1;
                $row->tested_at = \gmdate('Y-m-d H:i:s');
                $row->save();
            }
        }
    }

    /**
     * Метод смещает контрольные точки у тумб. Всего контрольных точек пять.
     * Значит берем клики периода рекалькуляции цтр и раскидываем равномерно по пяти точкам.
     * Затем выберем все тумбы, которые достигли необходимого значения, обнулим счетчик и сместим вправо на следующую точку.
     *
     * @return void
     */
    public function shiftHistoryCheckpoint(): void
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $recalculate_ctr_period = $settings->get('recalculate_ctr_period', static::RECALCULATE_CTR_PERIOD, 'videos');
        $showsCheckpointValue = (int) ceil($recalculate_ctr_period / 10);

        $thumbStats = VideosCategories::find()
            ->select(['video_id', 'category_id', 'current_shows', 'current_clicks', 'current_index'])
            ->where(['>=', 'current_shows', $showsCheckpointValue])
            ->asArray()
            ->all();

        if (empty($thumbStats)) {
            return;
        }

        foreach ($thumbStats as $thumbStat) {
            $currentIndex = (int) $thumbStat['current_index'];
            $checkPointNumber = $currentIndex % 10;

            VideosCategories::updateAll(
                [
                    'current_shows' => 0,
                    'current_clicks' => 0,
                    'current_index' => $currentIndex + 1,
                    "shows{$currentIndex}" => (int) $thumbStat['current_shows'],
                    "clicks{$currentIndex}" => (int) $thumbStat['current_clicks'],
                ],
                [
                    'video_id' => $thumbStat['video_id'],
                    'category_id' => $thumbStat['category_id'],
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
    public function resetOldTestedVideos(): void
    {
        $db = Yii::$app->db;
        $settings = Yii::$container->get(SettingsInterface::class);

        $thumbsPerPage = (int) $settings->get('items_per_page', static::ITEMS_PER_PAGE, 'videos');
        $testThumbsPercent = (int) $settings->get('test_items_percent', static::TEST_PERCENT, 'videos');
        $testPerPage = (int) ceil(($thumbsPerPage / 100) * $testThumbsPercent);
        $untouchablesThumbsNum = $thumbsPerPage - $testPerPage;

        $sql = "SELECT `category_id`, COUNT(*) - SUM(`is_tested`) as `tested_diff`
                FROM `videos_categories_map` as `vcm`
                LEFT JOIN `videos` as `v` ON (`vcm`.`video_id`=`v`.`video_id`)
                WHERE `v`.`published_at`<= NOW() AND `v`.`status` = 10
                GROUP BY `category_id`
                HAVING `tested_diff` < :testSpotsNum"; // = 0

        $categories = $db->createCommand($sql)
            ->bindValue(':testSpotsNum', $testPerPage)
            ->queryAll();

        foreach ($categories as $category) {
            $resetLimit = $testPerPage;//($testPerPage - (int) $category['tested_diff']) * 2;

            // найдем топовые тумбы в этой категории.
            $untouchablesThumbs = VideosCategories::find()
                ->alias('rs')
                ->select(['rs.video_id'])
                ->leftJoin(['v' => Video::tableName()], 'rs.video_id = v.video_id')
                ->where(['rs.category_id' => $category['category_id']])
                ->andWhere(['rs.is_tested' => 1])
                ->andWhere(['>', 'rs.ctr', 0])
                ->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
                ->andWhere(['v.status' => 10])
                ->orderBy(['rs.ctr' => SORT_DESC])
                ->limit($untouchablesThumbsNum)
                ->column();

            // найдем старые тумбы в категории. при этом исключим топовые (их ротировать нельзя).
            $resetThumbs = VideosCategories::find()
                ->alias('rs')
                ->select(['rs.video_id'])
                ->leftJoin(['v' => Video::tableName()], 'rs.video_id = v.video_id')
                ->where(['rs.category_id' => $category['category_id']])
                ->andWhere(['rs.is_tested' => 1])
                ->andFilterWhere(['NOT IN', 'rs.video_id', $untouchablesThumbs])
                ->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
                ->andWhere(['v.status' => 10])
                ->orderBy(['rs.tested_at' => SORT_DESC])
                ->limit($resetLimit)
                ->column();

            VideosCategories::updateAll($this->getResetFields(), [
                'video_id' => $resetThumbs,
                'category_id' => $category['category_id'],
            ]);
        }
    }

    /**
     * Resets statistics for rows with zero
     *
     * @return void
     */
    public function resetZeroCtr(): void
    {
        VideosCategories::updateAll($this->getResetFields(), [
            'is_tested' => 1,
            'ctr' => 0,
        ]);
    }

    /**
     * Циклический сброс данных ротатора на основе просмотров в категории.
     * В таблице в бд соответствует колонка `shows_before_reset`.
     *
     * @return void
     */
    public function cyclicResetByShows(): void
    {
        $settings = Yii::$container->get(SettingsInterface::class);
        $showsLimit = $settings->get('reset_rotation_period', 0, 'videos');

        if (empty($showsLimit)) {
            return;
        }

        VideosCategories::updateAll($this->getResetFields(), '`shows_before_reset` >= :limit AND `is_tested` = 1', [
            ':limit' => $showsLimit
        ]);
    }

    /**
     * Reset fields.
     *
     * @return array
     */
    private function getResetFields(): array
    {
        return [
            'is_tested' => 0,
            'tested_at' => null,
            'shows_before_reset' => 0,
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
            'shows5' => 0,
            'clicks5' => 0,
            'shows6' => 0,
            'clicks6' => 0,
            'shows7' => 0,
            'clicks7' => 0,
            'shows8' => 0,
            'clicks8' => 0,
            'shows9' => 0,
            'clicks9' => 0,
        ];
    }
}
