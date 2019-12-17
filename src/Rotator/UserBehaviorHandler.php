<?php
namespace SK\VideoModule\Rotator;

use Yii;
use yii\db\Expression;
use SK\VideoModule\Model\RotationStats;
use RS\Component\Core\Settings\SettingsInterface;

class UserBehaviorHandler
{
    /**
     * App user settings
     *
     * @var SettingsInterface
     */
    protected $settings;

    protected $isCrawler = false;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;

        $crawlerDetect = Yii::$container->get('crawler.detect');
        $this->isCrawler = $crawlerDetect->isCrawler();
    }

    /**
     * Обработка различных данных пришедшей статистики от юзера.
     *
     * @param UserBehaviorStatistic $statistic
     * @return void
     */
    public function handle(UserBehaviorStatistic $statistic)
    {
        if ($this->isCrawler/* || $this->settings->get('internal_register_activity', true, 'videos')*/) {
            return '';
        }

        // Обработка кликов по в категории.
        if (!empty($statistic->categoriesClicked)) {
            $this->handleCategoriesClicked($statistic->categoriesClicked);
        }

        // Обработка кликов по видео, в категории.
        if (null !== $statistic->fromCategory && !empty($statistic->videosClicked)) {
            $this->handleVideosClicked($statistic->videosClicked, $statistic->fromCategory);
        }

        // Обработка показанных видео на экране, в категории.
        if (null !== $statistic->fromCategory && !empty($statistic->videosViewed)) {
            $this->handleVideosViewed($statistic->videosViewed, $statistic->fromCategory);
        }
    }

    /**
     * Обработка кликов в видео в категориях. Учет кликов по идам видео.
     *
     * @param array $videosIds
     * @param int $categoryId
     * @return void
     */
    protected function handleVideosClicked(array $videosIds, $categoryId)
    {
        RotationStats::updateAllCounters(['current_clicks' => 1], ['video_id' => $videosIds, 'category_id' => $categoryId]);
    }

    /**
     * Учет показов видео на экране в категориях.
     *
     * @param array $videosIds
     * @param int $categoryId
     * @return void
     */
    protected function handleVideosViewed(array $videosIds, $categoryId)
    {
        RotationStats::updateAllCounters(['current_shows' => 1], ['video_id' => $videosIds, 'category_id' => $categoryId]);
    }

    /**
     * Обработка кликов в категории. Учет кликов по идам категорий.
     *
     * @param array $categoriesIds
     * @return void
     */
    protected function handleCategoriesClicked(array $categoriesIds)
    {
        $db = Yii::$app->db;

        $dateTime = new \DateTime('now', new \DateTimeZone('utc'));
        $currentDate = $dateTime->format('Y-m-d');
        $currentHour = $dateTime->format('H');

        foreach ($categoriesIds as $categoryId) {
            $db->createCommand()->upsert('videos_categories_stats', [
                'category_id' => (int) $categoryId,
                'date' => $currentDate,
                'hour' => $currentHour,
                'clicks' => 1,
            ], [
                'clicks' => new Expression('{{clicks}} + 1'),
            ])
            ->execute();
        }
    }
}
