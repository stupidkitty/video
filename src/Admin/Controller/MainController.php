<?php
namespace SK\VideoModule\Admin\Controller;

use Yii;
use yii\helpers\Url;
use yii\web\Request;
use yii\web\Controller;
use yii\base\DynamicModel;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use SK\VideoModule\Model\Video;
use RS\Component\User\Model\User;
use SK\VideoModule\Model\Category;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Model\RotationStats;
use SK\VideoModule\Admin\Form\VideoForm;
use SK\VideoModule\Admin\Form\VideoFilterForm;
use SK\VideoModule\Service\Video as VideoService;
use SK\VideoModule\Admin\Form\VideosBatchActionsForm;

/**
 * MainController implements the CRUD actions for Video model.
 */
class MainController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
           'access' => [
               'class' => AccessControl::class,
               'rules' => [
                   [
                       'allow' => true,
                       'roles' => ['@'],
                   ],
               ],
           ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'batch-delete' => ['post'],
                ],
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
        if (in_array($action->id, ['index'], true)) {
            Url::remember('', 'actions-redirect');
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all Video models.
     * @return mixed
     */
    public function actionIndex($page = 0)
    {
        $filerForm = new VideoFilterForm();

        $dataProvider = $filerForm->search($this->getRequest()->get());

        return $this->render('index', [
            'page' => $page,
            'filterForm' => $filerForm,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Video model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $video = $this->findById($id);

        $rotationStats = RotationStats::find()
            ->with(['image', 'category'])
            ->where(['video_id' => $video->getId()])
            ->orderBy(['ctr' => SORT_DESC])
            ->all();

        $thumbsRotationStats = [];

        foreach ($rotationStats as $item) {
            if (empty($thumbsRotationStats[$item->image->getId()]['image'])) {
                $thumbsRotationStats[$item->image->getId()]['image'] = $item->image;
            }

            $thumbsRotationStats[$item->image->getId()]['categories'][] = $item;
        }

        $statusLabel = $this->getStatusNames();

        return $this->render('view', [
            'video' => $video,
            'statusLabel' => $statusLabel,
            'thumbsRotationStats' => $thumbsRotationStats,
        ]);
    }

    /**
     * Creates a new Video model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $video = new Video;

        $form = new VideoForm([
            'categories_ids' => ArrayHelper::getColumn($video->categories, 'category_id'),
        ]);
        $form->setAttributes($video->getAttributes());

        if ($form->load($this->getRequest()->post()) && $form->isValid()) {
            $currentDatetime = gmdate('Y-m-d H:i:s');
            $videoService = new VideoService;

            $video->setAttributes($form->getAttributes());
            $video->generateSlug($form->slug);
            $video->updated_at = $currentDatetime;
            $video->created_at = $currentDatetime;

            $videoService->updateCategoriesByIds($video, $form->categories_ids);

            Yii::$app->session->addFlash('error', 'Сервис временно не работает');

            return $this->redirect(Url::previous('actions-redirect'));
        }

        $categoriesOptionsList = Category::find()
            ->select('title')
            ->indexBy('category_id')
            ->column();

        $userOptionsList = User::find()
            ->select('username')
            ->indexBy('user_id')
            ->column();

        $statusesOptionsList = $this->getStatusNames();

        return $this->render('create', [
            'video' => $video,
            'form' => $form,
            'categoriesOptionsList' => $categoriesOptionsList,
            'userOptionsList' => $userOptionsList,
            'statusesOptionsList' => $statusesOptionsList,
        ]);
    }

    /**
     * Updates an existing Video model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $video = $this->findById($id);

        $form = new VideoForm([
            'categories_ids' => ArrayHelper::getColumn($video->categories, 'category_id'),
        ]);
        $form->setAttributes($video->getAttributes());

        if ($form->load($this->getRequest()->post()) && $form->isValid()) {
            $currentDatetime = gmdate('Y-m-d H:i:s');
            $videoService = new VideoService;

            $video->setAttributes($form->getAttributes());
            $video->generateSlug($form->slug);
            $video->updated_at = $currentDatetime;

            $video->save();

            $videoService->updateCategoriesByIds($video, $form->categories_ids);

            return $this->redirect(Url::previous('actions-redirect'));
        }

        $categoriesOptionsList = Category::find()
            ->select('title')
            ->indexBy('category_id')
            ->column();

        $userOptionsList = User::find()
            ->select('username')
            ->indexBy('user_id')
            ->column();

        $statusesOptionsList = $this->getStatusNames();

        return $this->render('update', [
            'video' => $video,
            'form' => $form,
            'categoriesOptionsList' => $categoriesOptionsList,
            'usersOptionsList' => $userOptionsList,
            'statusesOptionsList' => $statusesOptionsList,
        ]);
    }

    /**
     * Deletes an existing Video model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $video = $this->findById($id);

        $videoService = new VideoService;

        if ($videoService->delete($video)) {
            Yii::$app->session->addFlash('success', Yii::t('videos', 'Видео "{title}" удалено', ['title' => $video->title]));
        }

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     *
     */
    public function actionBatchDelete()
    {
        $ajaxForm = new DynamicModel(['videos_ids']);

        $ajaxForm->addRule('videos_ids', 'each', ['rule' => ['integer']]);
        $ajaxForm->addRule('videos_ids', 'filter', ['filter' => 'array_filter']);
        $ajaxForm->addRule('videos_ids', 'required', ['message' => 'Videos not select']);

        $ajaxForm->load($this->getRequest()->post(), '');
        // Валидация массива идентификаторов видео.
        if (!$ajaxForm->validate()) {
            return $this->asJson([
                'error' => [
                    'message' => Yii::t('videos', 'Deletion failure'),
                ]
            ]);
        }

        $videoService = new VideoService;
        $deletedNum = $videoService->deleteById($ajaxForm->videos_ids);
        /*$videosQuery = Video::find()
            ->where(['video_id' => $ajaxForm->videos_ids]);

        $deletedNum = 0;
        foreach ($videosQuery->batch(20) as $videos) {
            foreach ($videos as $video) {
                if ($video->delete()) {
                    $deletedNum ++;
                }
            }
        }*/

        return $this->asJson([
            'message' => Yii::t('videos', '<b>{num}</b> videos deleted', ['num' => $deletedNum])
        ]);
    }

    /**
     *
     */
    public function actionBatchActions()
    {
        $form = new VideosBatchActionsForm();

        if ($form->load($this->getRequest()->post()) && $form->validate()) {
            try {
                $form->handle();

                return $this->asJson([
                    'message' => 'Success'
                ]);
            } catch (\Exception $e) {

                return $this->asJson([
                    'error' => [
                        'message' => $e->getMessage(),
                    ]
                ]);
            }
        }

        return $this->renderPartial('batch-actions', [
            'form' => $form,
        ]);
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
            ->with(['poster', 'categories', 'screenshots'])
            ->where(['video_id' => $id])
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $video;
    }

    /**
     * Возвращает список статусов видео
     *
     * @return array
     */
    protected function getStatusNames()
    {
        return [
    		Video::STATUS_DISABLED => Yii::t('videos', 'status_disabled'),
    		Video::STATUS_ACTIVE => Yii::t('videos', 'status_active'),
    		Video::STATUS_MODERATE => Yii::t('videos', 'status_moderate'),
    		Video::STATUS_DELETED => Yii::t('videos', 'status_deleted'),
    	];
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
