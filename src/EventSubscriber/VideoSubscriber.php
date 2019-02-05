<?php
namespace SK\VideoModule\EventSubscriber;

use Yii;
use yii\web\Request;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\RotationStats;
use SK\VideoModule\Model\VideosRelatedMap;
use SK\VideoModule\Model\VideosCategoriesMap;

final class VideoSubscriber
{
    /**
     * Событие должно подключаться к удаляемому объекту.
     */
    public static function onDelete($event)
    {
        $video = $event->sender;

        $images = Image::find()
            ->where(['video_id' => $video->getId()])
            ->all();

        foreach ($images as $image) {
            $image->delete();
        }

        VideosCategoriesMap::deleteAll(['video_id' => $video->getId()]);
        VideosRelatedMap::deleteAll(['video_id' => $video->getId()]);
        VideosRelatedMap::deleteAll(['related_id' => $video->getId()]);
        RotationStats::deleteAll(['video_id' => $video->getId()]);
    }

    /**
     * Событие при показе одного видео.
     * Регистрирует показ. Также регистрирует клик, если посетитель перешел с категории.
     *
     * @param \yii\base\Event $event
     */
    public static function onView($event)
    {
        $crawlerDetect = Yii::$container->get('crawler.detect');
        $request = Yii::$app->getRequest();

        if ($crawlerDetect->isCrawler()) {
            return;
        }

        $video_id = isset($event->data['video_id']) ? $event->data['video_id'] : 0;
        $image_id = isset($event->data['image_id']) ? $event->data['image_id'] : 0;

        // Обновление просмотра
        Video::updateAllCounters(['views' => 1], ['video_id' => $video_id]);

        // если рефера нет, не учитываем этот трафик.
        if (null === $request->getReferrer()) {
            return;
        }

        $urlParts = parse_url($request->getReferrer());
        $currentHostName = $request->getHostName();

        // также если рефер не с сайта, нет смысла учитывать.
        if ($urlParts['host'] !== $currentHostName) {
            return;
        }

        // Анализируем рефер
        $request = new Request([
            'baseUrl' => Yii::$app->urlManager->baseUrl,
            'url' => $urlParts['path'],
        ]);

        $route = Yii::$app->urlManager->parseRequest($request);

        // Определим, был ли клик со страницы категории.
        // Определим, был ли клик со страницы категории.
        if (
            $route[0] === 'videos/category/ctr'
            || $route[0] === 'videos/category/date'
            || $route[0] === 'videos/category/views'
            || $route[0] === 'videos/category/likes'
            || $route[0] === 'videos/category/index'
        ) {
            $slug = isset($route[1]['slug']) ? (string) $route[1]['slug'] : null;
            $cid = isset($route[1]['id']) ? (int) $route[1]['id'] : null;

            $category_id = Category::find()
                ->select('category_id')
                ->orFilterWhere([
                    'slug' => $slug,
                    'id' => $cid,
                ])
                ->andWhere(['enabled' => 1])
                ->scalar();

            // Аадейт счетчика просмотров видео
            if (!empty($category_id)) {
                RotationStats::updateAllCounters(['current_clicks' => 1], ['video_id' => $video_id, 'category_id' => $category_id, 'image_id' => $image_id]);
            }
        }
    }

    /**
     * Событие при показе нескольких видео в категории.
     * Регистрирует показ.
     *
     * @param \yii\base\Event $event
     */
    public static function onShowCategoryThumbs($event)
    {
        $crawlerDetect = Yii::$container->get('crawler.detect');

        if ($crawlerDetect->isCrawler()) {
            return;
        }

        if (empty($event->data['images_ids']) || empty($event->data['category_id'])) {
            return;
        }

        RotationStats::updateAllCounters(['current_shows' => 1], ['image_id' => $event->data['images_ids'], 'category_id' => $event->data['category_id']]);

        // Обновление клика по категории
        $referHost = parse_url(Yii::$app->request->getReferrer(), PHP_URL_HOST);
        $currentHost = Yii::$app->request->getHostName();

        if ($referHost === $currentHost && $event->data['page'] <= 1) {
            $dateTime = new \DateTime('now', new \DateTimeZone('utc'));

            $currentDate = $dateTime->format('Y-m-d');
            $currentHour = $dateTime->format('H');

            $sql = "        INSERT INTO `videos_categories_stats` (`category_id`, `date`, `hour`)
                                 VALUES (:category_id, :current_date, :current_hour)
                ON DUPLICATE KEY UPDATE `clicks`=`clicks`+1";

            Yii::$app->db->createCommand($sql)
               ->bindValues([
                   'category_id' => $event->data['category_id'],
                   'current_date' => $currentDate,
                   'current_hour' => $currentHour,
               ])
               ->execute();
        }
    }
}
