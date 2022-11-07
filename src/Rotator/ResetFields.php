<?php

namespace SK\VideoModule\Rotator;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideoInterface;
use SK\VideoModule\Model\VideosCategories;
use yii\db\Exception;

class ResetFields
{
    private SettingsInterface $settings;

    /**
     * @var int Default test item period (test shows).
     */
    public const TEST_ITEM_PERIOD = 200;

    /**
     * @var int default thumbs per page;
     */
    public const ITEMS_PER_PAGE = 24;

    /**
     * @var int default test thumbs percent
     */
    public const TEST_PERCENT = 15;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Сбрасывает ротацию старых видео, если в категории больше не осталось что ротировать.
     * Пропускает топовые видео с 1-й страницы.
     *
     * @return void
     * @throws Exception
     */
    public function resetOldTestedVideos(): void
    {
        $db = VideosCategories::getDb();

        $thumbsPerPage = (int) $this->settings->get('items_per_page', static::ITEMS_PER_PAGE, 'videos');
        $testThumbsPercent = (int) $this->settings->get('test_items_percent', static::TEST_PERCENT, 'videos');
        $testPerPage = (int) ceil(($thumbsPerPage / 100) * $testThumbsPercent);
        $untouchablesThumbsNum = $thumbsPerPage - $testPerPage;

       /* $sql = '
            SELECT  `category_id`, COUNT(*) as `total_num`
            FROM `videos_categories_map` as `vcm`
            WHERE `vcm`.`video_id` NOT IN (
                SELECT `video_id`
                FROM `videos`
                WHERE `status` != :status
            )
            GROUP BY `category_id`
        ';

        $totalCounters = $db->createCommand($sql)
            ->bindValue(':status', VideoInterface::STATUS_ACTIVE)
            ->queryAll();

        $sql = '
            SELECT  `category_id`, COUNT(*) as `tested_num`
            FROM `videos_categories_map` as `vcm`
            WHERE `vcm`.`is_tested`=1 AND `vcm`.`video_id` NOT IN (
                SELECT `video_id`
                FROM `videos`
                WHERE `status` != :status
            )
            GROUP BY `category_id`
        ';

        $rawTestedCounters = $db->createCommand($sql)
            ->bindValue(':status', VideoInterface::STATUS_ACTIVE)
            ->queryAll();
        $testedCounters = [];
        foreach ($rawTestedCounters as $row) {
            $categoryId = (int) $row['category_id'];
            $testedCounters[$categoryId] = (int) $row['tested_num'];
        }

        $categories = \array_reduce($totalCounters, function ($acc, $row) use ($testedCounters, $testPerPage) {
            $categoryId = (int) $row['category_id'];
            $totalItems = (int) $row['total_num'];
            $testedItems = $testedCounters[$categoryId] ?? 0;
            $diff = $totalItems - $testedItems;

            if ($diff < $testPerPage) {
                $acc[] = ['category_id' => $categoryId, 'tested_diff' => $diff];
            }

            return $acc;
        }, []);*/

        $sql = '
            SELECT `category_id`, MAX(`total`) as `total_num`, MAX(`tested`) as `tested_num`
            FROM (
                SELECT  `category_id`, COUNT(*) as `total`, 0 as `tested`
                FROM `videos_categories_map` as `vcm`
                WHERE `vcm`.`video_id` NOT IN (
                    SELECT `video_id`
                    FROM `videos`
                    WHERE `status` != :status
                )
                GROUP BY `category_id`
                UNION
                SELECT  `category_id`, 0 as `total`, COUNT(*) as `tested`
                FROM `videos_categories_map` as `vcm`
                WHERE `is_tested` = 1 AND `vcm`.`video_id` NOT IN (
                    SELECT `video_id`
                    FROM `videos`
                    WHERE `status` != :status
                )
                GROUP BY `category_id`
            ) as `x`
            GROUP BY `category_id`
            HAVING (`total_num` - `tested_num`) < :testSpotsNum
        ';

        $categories = $db->createCommand($sql)
            ->bindValue(':testSpotsNum', $testPerPage)
            ->bindValue(':status', VideoInterface::STATUS_ACTIVE)
            ->queryAll();

        if (\count($categories) === 0) {
            return;
        }

        $transaction = $db->beginTransaction();
        try {
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
                    //->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
                    ->andWhere(['v.status' => VideoInterface::STATUS_ACTIVE])
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
                    //->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
                    ->andWhere(['v.status' => VideoInterface::STATUS_ACTIVE])
                    ->orderBy(['rs.tested_at' => SORT_DESC])
                    ->limit($resetLimit)
                    ->column();

                VideosCategories::updateAll($this->getResetFields(), [
                    'video_id' => $resetThumbs,
                    'category_id' => $category['category_id'],
                ]);
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
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
        $showsLimit = (int) $this->settings->get('reset_rotation_period', 0, 'videos');

        if (!$showsLimit) {
            return;
        }

        VideosCategories::updateAll($this->getResetFields(), '`shows_before_reset` >= :limit AND `is_tested` = 1', [
            ':limit' => $showsLimit
        ]);

        // Сбросим кеш страниц категорий
        //if ($numChangedRows > 0) {
            //TagDependency::invalidate(Yii::$app->cache, 'videos:categories');
        //}
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
            'tested_at' => \gmdate('Y-m-d H:i:s'),
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
