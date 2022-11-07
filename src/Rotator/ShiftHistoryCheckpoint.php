<?php

namespace SK\VideoModule\Rotator;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Model\VideosCategories;
use Yii;

class ShiftHistoryCheckpoint
{
    private SettingsInterface $settings;
    /**
     * @var int default recalculate ctr period (shows);
     */
    public const RECALCULATE_CTR_PERIOD = 2000;
    /**
     * @var int Number of points
     */
    public const POINTS_NUM = 10;

    /**
     * ShiftHistoryCheckpoint constructor
     *
     * @param SettingsInterface $settings
     */
    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Метод смещает контрольные точки у тумб. Всего контрольных точек десять.
     * Значит берем клики периода рекалькуляции цтр и раскидываем равномерно по десяти точкам.
     * Затем выберем все тумбы, которые достигли необходимого значения, обнулим счетчик и сместим вправо на следующую точку.
     *
     * @return void
     */
    public function handle(): void
    {
        $recalculate_ctr_period = $this->settings->get('recalculate_ctr_period', static::RECALCULATE_CTR_PERIOD, 'videos');
        $showsCheckpointValue = (int) ceil($recalculate_ctr_period / static::POINTS_NUM);

        $statsQuery = VideosCategories::find()
            ->select(['video_id', 'category_id', 'current_shows', 'current_clicks', 'current_index'])
            ->where(['>=', 'current_shows', $showsCheckpointValue])
            ->asArray();

        $db = VideosCategories::getDb();

        $transaction = $db->beginTransaction();
        try {
            foreach ($statsQuery->batch() as $thumbStats) {
                foreach ($thumbStats as $thumbStat) {
                    $currentIndex = (int) $thumbStat['current_index'];
                    $checkPointNumber = $currentIndex % static::POINTS_NUM;

                    VideosCategories::updateAll(
                        [
                            'current_shows' => 0,
                            'current_clicks' => 0,
                            'current_index' => $currentIndex + 1,
                            "shows{$checkPointNumber}" => (int) $thumbStat['current_shows'],
                            "clicks{$checkPointNumber}" => (int) $thumbStat['current_clicks'],
                        ],
                        [
                            'video_id' => $thumbStat['video_id'],
                            'category_id' => $thumbStat['category_id'],
                        ]
                    );
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
        }
    }
}
