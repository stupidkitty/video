<?php
namespace SK\VideoModule\Api\Controller;

use Yii;
use yii\web\User;
use yii\web\Request;
use yii\rest\Controller;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Model\Video;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBearerAuth;
use SK\VideoModule\Api\Form\VideoForm;
use SK\VideoModule\Service\Video as VideoService;

/**
 * VideoController
 */
class VideoController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['view', 'index'],
            ],
        ];
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return Video::find()->limit(5)->all();
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $responseData = [];

        try {
            $video = $this->findById($id);
        } catch (\NotFoundHttpException $e) {
            $responseData['result']['video']['id'] = (int) $id;
            $responseData['result']['video']['errors'][] = $e->getMessage();

            return $responseData;
        }

        $videoData = [
            'id' => $video->video_id,
            'slug' => $video->slug,
            'title' => $video->title,
            'description' => $video->description,
            'orientation' => $video->orientation,
            'duration' => $video->duration,
            'videoPreview' => $video->video_preview,
            'embed' => $video->embed,
            'onIndex' => $video->on_index,
            'likes' => $video->likes,
            'dislikes' => $video->dislikes,
            'commentsNum' => $video->comments_num,
            'isHd' => $video->is_hd,
            'views' => $video->views,
            'publishedAt' => $video->published_at,
            'poster' => null,
            'categories' => [],
            'screenshots' => [],
        ];

        if ($video->hasPoster()) {
            $videoData['poster'] = [
                'id' => $video->poster->image_id,
                'path' => $video->poster->filepath,
                'source_url' => $video->poster->source_url,
            ];
        }

        if ($video->hasCategories()) {
            $videoData['categories'] = \array_map(function ($category) {
                return [
                    'id' => $category->category_id,
                    'slug' => $category->slug,
                    'title' => $category->title,
                    'h1' => $category->h1,
                ];
            }, $video->categories);
        }

        if ($video->hasScreenshots()) {
            $videoData['screenshots'] = \array_map(function ($screenshot) {
                return [
                    'id' => $screenshot->screenshot_id,
                    'path' => $screenshot->path,
                    'source_url' => $screenshot->source_url,
                ];
            }, $video->categories);
        }


        $responseData['result']['video'] = $videoData;

        return $responseData;
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$container->get(Request::class);
        $form = new VideoForm;

        if ($form->load($request->post()) && $form->isValid()) {
            $db = Yii::$app->db;
            $user = Yii::$container->get(User::class);

            $transaction = $db->beginTransaction();

            try {
                $video = new Video;
                $currentDatetime = gmdate('Y-m-d H:i:s');
                $videoService = new VideoService;

                $video->setAttributes($form->getAttributes());
                $video->generateSlug($form->slug);
                $video->user_id = $user->getId();
                $video->updated_at = $currentDatetime;
                $video->created_at = $currentDatetime;
                $video->published_at = $form->published_at;

                if (!$video->save()) {
                    return [
                        'error' => [
                            'code' => 422,
                            'message' => "Video \"{$video->title}\" create fail",
                            'errors' => $video->getErrorSummary(true),
                        ],
                    ];
                }

                // Добавление фото
                foreach ($form->images as $key => $imageUrl) {
                    $image = new Image([
                        'video_id' => $video->getId(),
                        'filepath' => $imageUrl,
                        'source_url' => $imageUrl,
                        'status' => 10,
                        'created_at' => $currentDatetime,
                    ]);

                    if ($image->save()) {
                        $video->addImage($image);

                        if (0 === $key) {
                            $video->setPoster($image);
                        }
                    } else {
                        throw new \Exception('Cannot add an image');
                    }
                }

                // Добавление категорий
                $videoService->updateCategoriesByIds($video, $form->categories_ids);

                $transaction->commit();

                return [
                    'message' => "Video \"{$video->title}\" created",
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();

                return [
                    'error' => [
                        'code' => 422,
                        'message' => $e->getMessage(),
                    ],
                ];
            }
        } else {
            return [
                'error' => [
                    'code' => 422,
                    'message' => "Cannot add video \"{$form->title}\"",
                    'errors' => $form->getErrorSummary(true),
                ],
            ];
        }
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $video = $this->findById($id);
        $request = Yii::$container->get(Request::class);

        $video->load(['Video' => $request->getBodyParams()]);

        if ($video->save()) {
            return [
                'message' => Yii::t('videos', 'Video "{title}" has been updated', ['title' => $video->title]),
            ];

        } else {
            return [
                'error' => [
                    'code' => 422,
                    'message' => Yii::t('videos', 'Video "{title}" update fail', ['title' => $video->title]),
                    'errors' => $video->getErrorSummary(true),
                ],
            ];
        }
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $video = $this->findById($id);
        $videoService = new VideoService;

        if ($videoService->delete($video)) {
            return '';
        }

        Yii::$app->getResponse()->setStatusCode(422);

        return [
            'error' => [
                'code' => 422,
                'message' => 'Can\'t delete video',
                'errors' => $video->getErrorSummary(true),
            ],
        ];
    }

    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findById($id)
    {
        $video = Video::find()
            ->alias('v')
            ->withViewRelations()
            ->whereIdOrSlug((int) $id)
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested video does not exist.');
        }

        return $video;
    }
}
