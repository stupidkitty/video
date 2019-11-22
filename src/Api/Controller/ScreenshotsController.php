<?php
namespace SK\VideoModule\Api\Controller;

use Yii;
use yii\web\Request;
use yii\rest\Controller;
use yii\filters\PageCache;
use SK\VideoModule\Model\Video;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Model\Screenshot;
use yii\filters\auth\HttpBearerAuth;
use SK\VideoModule\Api\Form\ScreenshotsForm;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Api\Form\DeleteScreenshotsForm;

/**
 * ScreenshotsController
 */
class ScreenshotsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['index'],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) Yii::$container->get(SettingsInterface::class)->get('enable_page_cache', false),
                'only' => ['index', 'view'],
                'duration' => 3200,
                'dependency' => [
                    'class' => 'yii\caching\TagDependency',
                    'tags' => 'videos:screenshots',
                ],
                'variations' => [
                    Yii::$app->language,
                    \implode(':', \array_values(Yii::$container->get(Request::class)->get())),
                ],
            ],
        ];
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionIndex($id = 0)
    {
        $responseData = [];

        $screenshots = Screenshot::find()
            ->select(['screenshot_id', 'path', 'source_url'])
            ->where(['video_id' => (int) $id])
            ->all();

        $screenshotsData = \array_map(function ($screenshot) {
            return [
                'id' => $screenshot->screenshot_id,
                'path' => $screenshot->path,
                'sourceUrl' => $screenshot->source_url,
            ];
        }, $screenshots);

        $responseData['result']['screenshots'] = $screenshotsData;

        return $responseData;
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionCreate($id)
    {
        $responseData['result']['createdScreenshots'] = [];
        $createdScreenshots = [];

        try {
            $video = $this->findVideoById($id);
        } catch (NotFoundHttpException $e) {
            $responseData['result']['createdScreenshots']['errors'][] = $e->getMessage();

            return $responseData;
        }

        $form = new ScreenshotsForm;

        if ($form->load($request->post()) && $form->isValid()) {
            foreach ($form->screenshots as $screenshot) {
                $preparedData = [
                    'video_id' => $video->video_id,
                    'path' => isset($screenshot['path']) ? trim($screenshot['path']) : '',
                    'source_url' => isset($screenshot['source_url']) ? trim($screenshot['source_url']) : '',
                    'created_at' => gmdate('Y-m-d H:i:s'),
                ];

                $newScreenshotRecord = new Screenshot($preparedData);

                if ($newScreenshotRecord->save()) {
                    $preparedData['id'] = $newScreenshotRecord->screenshot_id;
                } else {
                    $preparedData['errors'] = $$newScreenshotRecord->getErrorSummary(true);
                }

                $createdScreenshots[] = $preparedData;
            }
        }

        return  $responseData['result']['createdScreenshots'] = $createdScreenshots;
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $responseData['result']['deletedScreenshots'] = [];
        $deletedIds = [];

        try {
            $video = $this->findVideoById($id);
        } catch (NotFoundHttpException $e) {
            $responseData['result']['deletedScreenshots']['errors'][] = $e->getMessage();

            return $responseData;
        }

        $form = new DeleteScreenshotsForm;

        if ($form->load($request->getBodyParams()) && $form->isValid()) {
            $screenshots = Screenshot::find()
                ->where(['video_id' => $video->video_id, 'screenshot_id' => $form->screenshots_ids])
                ->all();

            foreach ($screenshots as $screenshot) {
                if ($screenshot->delete()) {
                    $deletedIds[] = $screenshot->screenshot_id;
                }
            }
        }

        return  $responseData['result']['deletedScreenshots'] = $deletedIds;
    }

    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findVideoById($id)
    {
        $video = Video::find()
            ->alias('v')
            ->whereIdOrSlug((int) $id)
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested video does not exist.');
        }

        return $video;
    }
}
