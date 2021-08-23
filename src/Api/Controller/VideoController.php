<?php

namespace SK\VideoModule\Api\Controller;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Api\Form\VideoForm;
use SK\VideoModule\Cache\PageCache;
use SK\VideoModule\Event\VideoShow;
use SK\VideoModule\EventSubscriber\VideoSubscriber;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Service\Video as VideoService;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

/**
 * VideoController
 */
class VideoController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            /*'corsFilter' => [
                'class' => Cors::class,
            ],*/
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['view', 'index', 'options', 'like', 'dislike'],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) Yii::$container->get(SettingsInterface::class)->get('enable_page_cache', false),
                'only' => ['view'],
                'duration' => 3200,
                'variations' => [
                    Yii::$app->language,
                    \implode(':', \array_values(Yii::$container->get(Request::class)->get())),
                ],
            ],
        ];
    }

    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ('view' === $action->id) {
            $response = Yii::$container->get(Response::class);

            $response->on($response::EVENT_AFTER_SEND, function () {
                $request = Yii::$container->get(Request::class);

                Yii::$app->trigger('video-show', new VideoShow([
                    'id' => (int) $request->get('id', 0),
                    'slug' => $request->get('slug', ''),
                ]));
            });

            Yii::$app->on('video-show', [VideoSubscriber::class, 'registerShow']);
        }

        return parent::beforeAction($action);
    }

    /**
     * Gets videos
     *
     * @return array
     */
    public function actionIndex(): array
    {
        return [];
    }

    /**
     * Gets video data
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function actionView(int $id): array
    {
        $responseData = [];

        try {
            $video = $this->findById($id);
        } catch (NotFoundHttpException $e) {
            $responseData['result']['video']['id'] = $id;
            $responseData['result']['video']['errors'][] = $e->getMessage();

            return $responseData;
        }

        $videoData = [
            'id' => $video->video_id,
            'slug' => $video->slug,
            'title' => $video->title,
            'description' => $video->description,
            'searchField' => $video->search_field,
            'orientation' => $video->orientation,
            'duration' => $video->duration,
            'videoPreview' => $video->video_preview,
            'embed' => $video->embed,
            'onIndex' => $video->on_index,
            'likes' => $video->likes,
            'dislikes' => $video->dislikes,
            'commentsNum' => $video->comments_num,
            'isHd' => $video->is_hd,
            'noindex' => $video->noindex,
            'nofollow' => $video->nofollow,
            'views' => $video->views,
            'publishedAt' => (new \DateTime($video->published_at))->format('Y-m-d\TH:i:s\Z'),
            'custom1' => $video->custom1,
            'custom2' => $video->custom2,
            'custom3' => $video->custom3,
            'maxCtr' => $video->max_ctr,
            'poster' => null,
            'categories' => [],
        ];

        if ($video->hasPoster()) {
            $videoData['poster'] = [
                'id' => $video->poster->image_id,
                'path' => $video->poster->filepath,
                'sourceUrl' => $video->poster->source_url,
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

        $responseData['result']['video'] = $videoData;

        return $responseData;
    }

    /**
     * Create video
     *
     * @param Request $request
     * @param User $user
     * @return array[]|string[]
     */
    public function actionCreate(Request $request, User $user): array
    {
        $form = new VideoForm();

        if ($form->load($request->post()) && $form->isValid()) {
            $db = Yii::$app->db;

            $transaction = $db->beginTransaction();

            try {
                $video = new Video();
                $videoService = new VideoService();

                $video->setAttributes($form->getAttributes());
                $video->generateSlug($form->slug);
                $video->user_id = $user->getId();
                $video->published_at = $form->published_at;

                if ($video->search_field === null || $video->search_field === '') {
                    $video->search_field = "{$video->title} {$video->description}";
                }

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
     * @param Request $request
     * @param int $id
     * @return array|array[]
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function actionUpdate(Request $request, int $id): array
    {
        $video = $this->findById($id);

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
     * Delete video
     *
     * @return array[]|string
     * @throws NotFoundHttpException
     */
    public function actionDelete(Response $response, int $id)
    {
        $video = $this->findById($id);
        $videoService = new VideoService();

        if ($videoService->delete($video)) {
            return '';
        }

        $response->setStatusCode(422);

        return [
            'error' => [
                'code' => 422,
                'message' => 'Can\'t delete video',
                'errors' => $video->getErrorSummary(true),
            ],
        ];
    }

    /**
     * Лайк видео
     *
     * @param int $id
     * @return string
     */
    public function actionLike(int $id): string
    {
        $video_id = $id;

        Video::updateAllCounters(['likes' => 1], ['video_id' => $video_id]);

        return '';
    }

    /**
     * Дизлайк видео
     *
     * @param int $id
     * @return string
     */
    public function actionDislike(int $id): string
    {
        $video_id = $id;

        Video::updateAllCounters(['dislikes' => 1], ['video_id' => $video_id]);

        return '';
    }

    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findById(int $id): Video
    {
        $video = Video::find()
            ->alias('v')
            ->withViewRelations()
            ->whereIdOrSlug($id)
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested video does not exist.');
        }

        return $video;
    }
}
