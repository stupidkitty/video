<?php

namespace SK\VideoModule\Rotator;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Model\VideosCategories;
use yii\db\Exception;

class MarkAsTestedThumbs
{
    private SettingsInterface $settings;

    /**
     * @var int Default test item period (test shows).
     */
    public const TEST_ITEM_PERIOD = 200;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Устанавливает флаг "тестировано" у записи
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $test_item_period = (int) $this->settings->get('test_item_period', static::TEST_ITEM_PERIOD, 'videos');

        // Завершим тестовый период у тумб, если набралась необходимая статистика.
        $db = VideosCategories::getDb();

        $sql = '
            UPDATE `videos_categories_map`
            SET `is_tested` = 1, `tested_at` = NOW()
            WHERE `is_tested` = 0 AND `total_shows` >= :test_item_period
        ';

        $db
            ->createCommand($sql)
            ->bindValue(':test_item_period', $test_item_period)
            ->execute();
    }
}
