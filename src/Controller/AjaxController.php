<?php

namespace SK\VideoModule\Controller;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\EventSubscriber\VideoSubscriber;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosCategories;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

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

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * @param int $id
     * @param SettingsInterface $settings
     * @return Response
     */
    public function actionGetVideo(int $id, SettingsInterface $settings)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET');
        header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

        $video = Video::find()
            ->alias('v')
            ->withViewRelations()
            ->whereIdOrSlug($id)
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
        } else {
            Video::updateAllCounters(['views' => 1], ['video_id' => $id]);
        }

        return $this->asJson($video);
    }

    /**
     * Update click by category_id
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\di\NotInstantiableException
     */
    public function actionCategoryClick(Request $request, Response $response, SettingsInterface $settings)
    {
        $crawlerDetect = $this->get('crawler.detect');

        if ($crawlerDetect->isCrawler()) {
            $response->setStatusCode(404);

            return '';
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $response->setStatusCode(404);

            return '';
        }

        $db = Yii::$app->db;

        $categoryId = (int) $request->post('id', 0);
        $dateTime = new \DateTime('now', new \DateTimeZone('utc'));

        $currentDate = $dateTime->format('Y-m-d');
        $currentHour = (int) $dateTime->format('H');

        $sql = "        INSERT INTO `videos_categories_stats` (`category_id`, `date`, `hour`)
                             VALUES (:category_id, :current_date, :current_hour)
            ON DUPLICATE KEY UPDATE `clicks`=`clicks`+1";

        $db->createCommand($sql)
            ->bindValues([
                'category_id' => $categoryId,
                'current_date' => $currentDate,
                'current_hour' => $currentHour,
            ])
            ->execute();

        return '';
    }

    /**
     * Счетчик кликов по тумбе в категории.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionVideoClick(Request $request, Response $response, SettingsInterface $settings)
    {
        $crawlerDetect = $this->get('crawler.detect');

        if ($crawlerDetect->isCrawler()) {
            $response->setStatusCode(404);

            return '';
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $response->setStatusCode(404);

            return '';
        }

        $video_id = (int) $request->post('video_id', 0);
        $category_id = (int) $request->post('category_id', 0);

        if (!$video_id || !$category_id) {
            $response->setStatusCode(404);

            return '';
        }

        // Апдейт статы ротации тумбы
        VideosCategories::updateAllCounters(['current_clicks' => 1], ['video_id' => $video_id, 'category_id' => $category_id]);

        return '';
    }

    /**
     * Учет показов тумб на странице категории.
     *
     * @param Request $request
     * @param Response $response
     * @param SettingsInterface $settings
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionThumbsLog(Request $request, Response $response, SettingsInterface $settings)
    {
        $crawlerDetect = $this->get('crawler.detect');

        if ($crawlerDetect->isCrawler()) {
            $response->setStatusCode(404);

            return '';
        }

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $response->setStatusCode(404);

            return '';
        }

        $category_id = (int) $request->post('category_id', 0);
        $videos_ids = $request->post('videos_ids', '');
        $videos_ids = \json_decode($videos_ids, true);

        if (!$category_id || empty($videos_ids)) {
            return '';
        }

        VideosCategories::updateAllCounters(['current_shows' => 1, 'shows_before_reset' => 1], ['video_id' => $videos_ids, 'category_id' => $category_id]);

        return '';
    }

    /**
     * Video has been liked
     *
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function actionLike(Request $request, Response $response)
    {
        $video_id = (int) $request->post('id', 0);

        if ($video_id === 0) {
            $response->setStatusCode(404);

            return '';
        }

        Video::updateAllCounters(['likes' => 1], '`video_id` = :video_id', [':video_id' => $video_id]);

        return '';
    }

    /**
     * Video has been disliked
     *
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function actionDislike(Request $request, Response $response)
    {
        $video_id = (int) $request->post('id', 0);

        if ($video_id === 0) {
            $response->setStatusCode(404);

            return '';
        }

        Video::updateAllCounters(['dislikes' => 1], '`video_id` = :video_id', [':video_id' => $video_id]);

        return '';
    }

    /**
     * Get instance by tag name form DI container
     *
     * @param string $name
     * @return object
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function get(string $name): object
    {
        return Yii::$container->get($name);
    }
}
