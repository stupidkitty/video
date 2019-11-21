<?php
namespace SK\VideoModule\Api\Controller;

use Yii;
use yii\base\Event;
use yii\web\Request;
use yii\rest\Controller;
use yii\filters\PageCache;
use SK\VideoModule\Model\Category;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBearerAuth;
use SK\VideoModule\Api\Form\VideoForm;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Service\Video as VideoService;

/**
 * VideoController
 */
class CategoriesController extends Controller
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
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) Yii::$container->get(SettingsInterface::class)->get('enable_page_cache', false),
                'only' => ['index', 'view'],
                'duration' => 3200,
                'dependency' => [
                    'class' => 'yii\caching\TagDependency',
                    'tags' => 'videos:categories',
                ],
                'variations' => [
                    Yii::$app->language,
                    \implode(':', \array_values(Yii::$container->get(Request::class)->get())),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeAction($action)
    {
        $request = Yii::$container->get(Request::class);

        Event::trigger(static::class, "action.{$action->id}", new Event(['data' => $request->get()]));

        return parent::beforeAction($action);
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $responseData = [];

        $categories = Category::find()
            ->select(['category_id', 'slug', 'image', 'title', 'description', 'h1', 'param1', 'param2', 'param3', 'videos_num', 'last_period_clicks', 'on_index'])
            ->where(['enabled' => 1])
            ->all();

        $categoriesData = \array_map(function ($category) {
            return [
                'id' => $category->category_id,
                'image' => $category->image,
                'slug' => $category->slug,
                'title' => $category->title,
                'description' => $category->description,
                'h1' => $category->h1,
                'param1' => $category->param1,
                'param2' => $category->param2,
                'param3' => $category->param3,
                'videosNum' => $category->videos_num,
                'lastPeriodClicks' => $category->last_period_clicks,
                'onIndex' => $category->on_index,
            ];
        }, $categories);

        $responseData['result']['categories'] = $categoriesData;

        return $responseData;
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
            $category = $this->findById($id);
        } catch (\NotFoundHttpException $e) {
            $responseData['result']['category']['id'] = (int) $id;
            $responseData['result']['category']['errors'][] = $e->getMessage();

            return $responseData;
        }

        $categoryData = [
            'id' => $category->category_id,
            'image' => $category->image,
            'slug' => $category->slug,
            'title' => $category->title,
            'metaTitle' => $category->meta_title,
            'metaDescription' => $category->meta_description,
            'description' => $category->description,
            'h1' => $category->h1,
            'seotext' => $category->seotext,
            'param1' => $category->param1,
            'param2' => $category->param2,
            'param3' => $category->param3,
            'videosNum' => $category->videos_num,
            'lastPeriodClicks' => $category->last_period_clicks,
            'onIndex' => $category->on_index,
            'enabled' => $category->enabled,
        ];

        $responseData['result']['category'] = $categoryData;

        return $responseData;
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        /*$request = Yii::$container->get(Request::class);
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
        }*/
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        /*$video = $this->findById($id);
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
        }*/
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        /*$video = $this->findById($id);
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
        ];*/
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findById($id)
    {
        $category = Category::find()
            ->where(['category_id' => (int) $id, 'enabled' => 1])
            ->one();

        if (null === $category) {
            throw new NotFoundHttpException('The requested category does not exist.');
        }

        return $category;
    }
}
