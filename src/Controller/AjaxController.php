<?php
namespace SK\VideoModule\Controller;

use Yii;
use yii\web\Request;
use yii\web\Controller;

use yii\filters\VerbFilter;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\RotationStats;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\EventSubscriber\VideoSubscriber;

/**
 * AjaxController представляет различные аякс действия.
 */
class AjaxController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'category-click' => ['post'],
                    'video-click' => ['post'],
                    'thumbs-log' => ['post'],
                    'like' => ['post'],
                    'dislike' => ['post'],
                    'get-video' => ['get'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actionGetVideo($id)
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $video = Video::find()
            ->alias('v')
            ->withViewRelations()
            ->whereIdOrSlug((int) $id)
            ->untilNow()
            ->onlyActive()
            ->asArray()
            ->one();

        if (null === $video) {
            return $this->asJson([
                'error' => [
                    'code' => 404,
                    'message' => 'The requested video does not exist.',
                ],
            ]);
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(self::EVENT_AFTER_ACTION, [VideoSubscriber::class, 'onView'], $video);
        }

        return $this->asJson($video);
    }


    /**
     * Update click by category_id
     *
     * @return mixed
     */
    public function actionCategoryClick()
    {
        $crawlerDetect = Yii::$container->get('crawler.detect');

        if ($crawlerDetect->isCrawler()) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        $settings = Yii::$container->get(SettingsInterface::class);

        if ($settings->get('internal_register_activity', true, 'videos')) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        $db = Yii::$app->db;

        $categoryId = $this->getRequest()->post('id', 0);
        $dateTime = new \DateTime('now', new \DateTimeZone('utc'));

        $currentDate = $dateTime->format('Y-m-d');
        $currentHour = $dateTime->format('H');

        $sql = "        INSERT INTO `videos_categories_stats` (`category_id`, `date`, `hour`)
                             VALUES (:category_id, :current_date, :current_hour)
            ON DUPLICATE KEY UPDATE `clicks`=`clicks`+1";

        $db->createCommand($sql)
           ->bindValues([
               'category_id' => (int) $categoryId,
               'current_date' => $currentDate,
               'current_hour' => (int) $currentHour,
           ])
           ->execute();

        return '';
    }

    /**
     * Счетчик кликов по тумбе в категории.
     *
     * @return mixed
     */
    public function actionVideoClick()
    {
        $crawlerDetect = Yii::$container->get('crawler.detect');

        if ($crawlerDetect->isCrawler()) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        $settings = Yii::$container->get(SettingsInterface::class);

        if ($settings->get('internal_register_activity', true, 'videos')) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        $db = Yii::$app->db;
        $request = $this->getRequest();

        $video_id = (int) $request->post('video_id', 0);
        $image_id = (int) $request->post('image_id', 0);
        $category_id = (int) $request->post('category_id', 0);

        if (!$video_id || !$image_id || !$category_id) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        // Апдейт счетчика просмотров видео
        $db->createCommand('UPDATE `videos` SET `views`=`views`+1 WHERE `video_id`=:video_id')
            ->bindParam(':video_id', $video_id)
            ->execute();

        // Апдейт статы ротации тумбы
        //RotationStats::updateAllCounters(['current_clicks' => 1], ['video_id' => $video_id, 'category_id' => $category['category_id'], 'image_id' => $image_id]);
        $db->createCommand('UPDATE `videos_stats` SET `current_clicks`=`current_clicks`+1 WHERE `video_id`=:video_id AND `category_id`=:category_id AND `image_id`=:image_id')
            ->bindParam(':video_id', $video_id)
            ->bindParam(':category_id', $category_id)
            ->bindParam(':image_id', $image_id)
            ->execute();
    }

    /**
     * Учет показов тумб на странице категории.
     *
     * @return mixed
     */
    public function actionThumbsLog()
    {
        $crawlerDetect = Yii::$container->get('crawler.detect');

        if ($crawlerDetect->isCrawler()) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        $settings = Yii::$container->get(SettingsInterface::class);

        if ($settings->get('internal_register_activity', true, 'videos')) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        $db = Yii::$app->db;
        $request = $this->getRequest();

        $category_id = (int) $request->post('category_id', 0);
        $images_ids = $request->post('images', '');
        $images_ids = json_decode($images_ids, true);

        if (!$category_id || empty($images_ids)) {
            return;
        }

        RotationStats::updateAllCounters(['current_shows' => 1], ['image_id' => $images_ids, 'category_id' => $category_id]);
    }

    /**
     * Лайк видео
     *
     * @return string
     */
    public function actionLike()
    {
        $video_id = (int) $this->getRequest()->post('id', 0);

        if (!$video_id) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        Video::updateAllCounters(['likes' => 1], '`video_id` = :video_id', [':video_id' => $video_id]);

        return '';
    }

    /**
     * Дизлайк видео
     *
     * @return string
     */
    public function actionDislike()
    {
        $video_id = (int) $this->getRequest()->post('id', 0);

        if (!$video_id) {
            Yii::$app->response->setStatusCode(404);

            return '';
        }

        $db = Yii::$app->db;

        Video::updateAllCounters(['dislikes' => 1], '`video_id` = :video_id', [':video_id' => $video_id]);

        return '';
    }

    /**
     * Get request class form DI container
     *
     * @return \yii\web\Request
     */
    protected function getRequest()
    {
        return Yii::$container->get(Request::class);
    }
}
