<?php
namespace SK\VideoModule\Controller\Api;

use Yii;
use yii\web\User;
use yii\base\Event;
use yii\web\Request;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBearerAuth;
use SK\VideoModule\Form\Api\VideoForm;
use SK\VideoModule\Model\RotationStats;
use SK\VideoModule\EventSubscriber\VideoSubscriber;

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
            /*'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],*/
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get', 'head'],
                    'view' => ['get', 'head'],
                    'create' => ['post'],
                    'update' => ['put', 'patch'],
                    'delete' => ['delete'],
                ],
            ],
            'corsFilter' => [
                'class' => Cors::class,
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::class,
            ],
        ];
    }

    /**
     * @param \yii\base\Action $action
     *
     * @return bool
     */
    public function beforeAction($action)
    {

        //Event::on(Video::class, Video::EVENT_BEFORE_INSERT, [VideoSubscriber::class, 'onCreate']);
        //Event::on(Video::class, Video::EVENT_BEFORE_UPDATE, [VideoSubscriber::class, 'onUpdate']);
        Event::on(Video::class, Video::EVENT_BEFORE_DELETE, [VideoSubscriber::class, 'onDelete']);

        return parent::beforeAction($action);
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
        $video = $this->findById($id);

        return $video;
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

                $video->setAttributes($form->getAttributes());
                $video->generateSlug($form->slug);
                $video->user_id = $user->getId();
                $video->updated_at = $currentDatetime;
                $video->created_at = $currentDatetime;
                $video->published_at = $form->published_at;

                if (!$video->save()) {
                    $errors = [];
                    foreach($video->getErrorSummary(true) as $message) {
                        $errors[] = $message;
                    }

                    return [
                        'error' => [
                            'code' => 422,
                            'message' => "Video \"{$video->title}\" create fail",
                            'errors' => $errors,
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
                $addCategories = Category::find()
                    ->where(['category_id' => $form->categories_ids])
                    ->all();

                foreach ($addCategories as $addCategory) {
                    $video->addCategory($addCategory);

                    if ($video->hasPoster()) {
                        RotationStats::addVideo($addCategory, $video, $video->poster, true);
                    }
                }

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
            $errors = [];
            foreach($form->getErrorSummary(true) as $message) {
                $errors[] = $message;
            }

            return [
                'error' => [
                    'code' => 422,
                    'message' => "Cannot add video \"{$form->title}\"",
                    'errors' => $errors,
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
            $errors = [];
            foreach($video->getErrorSummary(true) as $message) {
                $errors[] = $message;
            }

            return [
                'error' => [
                    'errors' => $errors,
                    'code' => 422,
                    'message' => Yii::t('videos', 'Video "{title}" update fail', ['title' => $video->title]),
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

        if ($video->delete()) {
            return '';
        }

        Yii::$app->getResponse()->setStatusCode(422);

        $errors = [];
        foreach($video->getErrorSummary(true) as $message) {
            $errors[] = $message;
        }

        return [
            'error' => [
                'code' => 422,
                'message' => 'Can\'t delete video',
                'errors' => $errors,
            ],
        ];
    }

    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findById($id)
    {
        $video = Video::find()
            ->where(['video_id' => $id])
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $video;
    }
}
