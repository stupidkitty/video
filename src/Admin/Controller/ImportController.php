<?php
namespace SK\VideoModule\Admin\Controller;

use Yii;
use yii\web\Request;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use SK\VideoModule\Model\Video;
use yii\data\ActiveDataProvider;
use RS\Component\User\Model\User;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Model\ImportFeed;
use SK\VideoModule\Csv\CategoryCsvHandler;
use SK\VideoModule\Admin\Form\VideosImport;
use SK\VideoModule\Admin\Form\CategoriesImportForm;

/**
 * ImportController
 */
class ImportController extends Controller
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
                    'delete-feed' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Импорт роликов через файл или текстовую форму
     *
     * @return mixed
     */
    public function actionVideos($preset = 0)
    {
        $request = $this->get(Request::class);
        $importFeed = ImportFeed::find()
            ->where(['feed_id' => $preset])
            ->one();

        if (!$importFeed instanceof ImportFeed) {
            $importFeed = new ImportFeed();
        }

        $model = new VideosImport($importFeed);

        $model->csv_file = UploadedFile::getInstance($model, 'csv_file');

        if ($model->load($request->post()) && $model->validate()) {
            $model->save();

            if (0 < $model->getImportedRowsNum()) {
                Yii::$app->session->setFlash('success', Yii::t('videos', '<b>{num}</b> videos added', ['num' => $model->getImportedRowsNum()]));
            }
        }

        $userListOptions = User::find()
            ->select('username')
            ->indexBy('user_id')
            ->column();

        $statusListOptions = Video::getStatuses();

        $presetListOptions = ImportFeed::find()
            ->select(['name'])
            ->indexBy('feed_id')
            ->column();

        return $this->render('videos', [
            'preset' => $preset,
            'model' => $model,
            'userListOptions' => $userListOptions,
            'statusListOptions' => $statusListOptions,
            'presetListOptions' => $presetListOptions,
        ]);
    }

    /**
     * Импорт категорий через файл или текстовую форму.
     *
     * @return mixed
     */
    public function actionCategories()
    {
        $request = $this->get(Request::class);
        $csvHandler = $this->get(CategoryCsvHandler::class);
        $form = new CategoriesImportForm;
        $isProcessed = false;

        if ($form->load($request->post()) && $form->isValid()) {
            $handlerConfig = $form->getData();
            $csvHandler->handle($handlerConfig);
            $isProcessed = true;
        }

        return $this->render('categories', [
            'isProcessed' => $isProcessed,
            'form' => $form,
            'failedItems' => $csvHandler->getFailedItems()
        ]);
    }

    /**
     * Lists all ImportFeed models.
     *
     * @return mixed
     */
    public function actionListFeeds()
    {
        $query = ImportFeed::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 500,
                'pageSize' => 500,
            ],
        ]);

        return $this->render('list_feeds', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new ImportFeed model.
     * If creation is successful, the browser will be redirected to the 'videos' page.
     *
     * @return mixed
     */
    public function actionAddFeed()
    {
        $request = $this->get(Request::class);
        $feed = new ImportFeed();

        if ($feed->load($request->post()) && $feed->save()) {
            return $this->redirect(['videos', 'preset' => $feed->feed_id]);
        }

        return $this->render('add_feed', [
            'feed' => $feed,
        ]);
    }

    /**
     * Редактирование существующего фида импорта
     *
     * @return mixed
     */
    public function actionUpdateFeed($id)
    {
        $request = $this->get(Request::class);
        $feed = $this->findById($id);

        if ($feed->load($request->post()) && $feed->save()) {
            return $this->redirect(['videos', 'preset' => $feed->feed_id]);
        }

        return $this->render('update_feed', [
            'feed' => $feed,
        ]);
    }

    /**
     * Удаление фида импорта
     *
     * @return mixed
     */
    public function actionDeleteFeed($id)
    {
        $feed = $this->findById($id);

        $name = $feed->name;

        if ($feed->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('videos', 'Feed "<b>{name}</b>" deleted', ['name' => $name]));
        }

        return $this->redirect(['list-feeds']);
    }
    /**
     * Удаление фида импорта
     *
     * @return mixed
     */
    public function findById($id)
    {
        $feed = ImportFeed::findById($id);

        if (!$feed instanceof ImportFeed) {
            throw new NotFoundHttpException('The requested feed does not exist.');
        }

        return $feed;
    }

    /**
     * Get request class form DI container
     *
     * @return \yii\web\Request
     */
    protected function get($name)
    {
        return Yii::$container->get($name);
    }
}
