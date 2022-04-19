<?php
namespace SK\VideoModule\Rotator;

use Yii;
use yii\db\Expression;
use SK\VideoModule\Model\VideosCategories;
use RS\Component\Core\Settings\SettingsInterface;

class UserBehaviorHandler
{
    /**
     * App user settings
     *
     * @var SettingsInterface
     */
    private SettingsInterface $settings;

    private $isCrawler = false;

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
        if ($this->isCrawler) {
            return;
        }

        // Обработка кликов по в категории.
        if (!empty($statistic->categoriesClicked)) {
            $this->handleCategoriesClicked($statistic->categoriesClicked);
        }

        // Обработка кликов по видео, в категории.
        if (!empty($statistic->videosClicked)) {
            $this->handleVideosClicked($statistic->videosClicked);
        }

        // Обработка показанных видео на экране, в категории.
        if (!empty($statistic->videosViewed)) {
            $this->handleVideosViewed($statistic->videosViewed);
        }
    }

    /**
     * Обработка кликов в видео в категориях. Учет кликов по идам видео.
     *
     * @param array $data Массив упакованной информации по тумбам.
     * @return void
     */
    protected function handleVideosClicked(array $data)
    {
        $inCategory = [];
        $thumbs = [];
        foreach($data as $item) {
            $video = \json_decode(\base64_decode($item), true);

            if (isset($video['id']) && isset($video['inCategoryId']) && 0 !== $categoryId = (int) $video['inCategoryId']) {
                $inCategory[$categoryId][] = [
                    'video_id' => (int) $video['id'],
                ];
            }

            if (isset($video['id']) && isset($video['imageId'])) {
                $thumbs['image_id'][] = (int) $video['imageId'];
                $thumbs['video_id'][] = (int) $video['id'];
            }
        }

        foreach ($inCategory as $key => $videosIds) {
            VideosCategories::updateAllCounters(['current_clicks' => 1], ['video_id' => $videosIds, 'category_id' => $key]);
        }

        //Image::updateAllCounters(['current_clicks' => 1], ['image_id' => $thumbs['image_id'], 'video_id' =>  $thumbs['video_id']]);
    }

    /**
     * Учет показов видео на экране в категориях.
     *
     * @param array $data
     * @return void
     */
    protected function handleVideosViewed(array $data)
    {
        $inCategory = [];
        $thumbs = [];
        foreach($data as $item) {
            $video = \json_decode(\base64_decode($item), true);

            if (isset($video['id']) && isset($video['inCategoryId']) && 0 !== $categoryId = (int) $video['inCategoryId']) {
                $inCategory[$categoryId][] = [
                    'video_id' => (int) $video['id'],
                ];
            }

            if (isset($video['id']) && isset($video['imageId'])) {
                $thumbs['image_id'][] = (int) $video['imageId'];
                $thumbs['video_id'][] = (int) $video['id'];
            }
        }

        foreach ($inCategory as $key => $videosIds) {
            VideosCategories::updateAllCounters(['current_shows' => 1, 'shows_before_reset' => 1], ['video_id' => $videosIds, 'category_id' => $key]);
        }

        //Image::updateAllCounters(['current_shows' => 1], ['image_id' => $thumbs['image_id'], 'video_id' =>  $thumbs['video_id']]);
    }

    /**
     * Обработка кликов в категории. Учет кликов по идам категорий.
     *
     * @param array $categoriesIds
     * @return void
     */
    protected function handleCategoriesClicked(array $data)
    {
        $categoriesIds = [];
        foreach ($data as $item) {
            $category = \json_decode(\base64_decode($item), true);

            if (isset($category['id']) && 0 !== $categoryId = (int) $category['id']) {
                $categoriesIds[] = $categoryId;
            }
        }

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
