<?php

namespace SK\VideoModule\Admin\Controller;

use RS\Component\User\Model\User;
use SK\VideoModule\Admin\Form\CategoriesImportForm;
use SK\VideoModule\Admin\Form\VideosImport;
use SK\VideoModule\Csv\CategoryCsvHandler;
use SK\VideoModule\Model\ImportFeed;
use SK\VideoModule\Model\Video;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * ImportController
 */
class ImportController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
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
     * @param Request $request
     * @param int $preset
     * @return string
     */
    public function actionVideos(Request $request, int $preset = 0): string
    {
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
     * @param Request $request
     * @param CategoryCsvHandler $csvHandler
     * @return string
     */
    public function actionCategories(Request $request, CategoryCsvHandler $csvHandler): string
    {
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
     * @return string
     */
    public function actionListFeeds(): string
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
     * @return Response|string
     */
    public function actionAddFeed(Request $request)
    {
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
     * @param Request $request
     * @param int $id
     * @return Response|string
     * @throws NotFoundHttpException
     */
    public function actionUpdateFeed(Request $request, int $id)
    {
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
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteFeed(int $id): Response
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
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findById(int $id)
    {
        $feed = ImportFeed::findById($id);

        if (!$feed instanceof ImportFeed) {
            throw new NotFoundHttpException('The requested feed does not exist.');
        }

        return $feed;
    }
}
