<?php

namespace SK\VideoModule\Admin\Controller;

use SK\VideoModule\Csv\CategoryCsvHandler;
use SK\VideoModule\Model\CategoryImportFeed;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

class CategoriesImportFeedsController extends Controller
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
     * Lists all CategoryImportFeed models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $query = CategoryImportFeed::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 500,
                'pageSize' => 500,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Creates a new CategoryImportFeed model.
     * If creation is successful, the browser will be redirected to the 'videos' page.
     *
     * @return Response|string
     */
    public function actionCreate(Request $request)
    {
        $feed = new CategoryImportFeed();

        if ($feed->load($request->post()) && $feed->save()) {
            return $this->redirect(['/admin/videos/import-categories', 'preset' => $feed->feed_id]);
        }

        return $this->render('create', [
            'feed' => $feed,
        ]);
    }

    /**
     * Редактирование существующего фида
     *
     * @param Request $request
     * @param int $id
     * @return Response|string
     * @throws NotFoundHttpException
     */
    public function actionUpdate(Request $request, int $id)
    {
        $feed = $this->findById($id);

        if ($feed->load($request->post()) && $feed->save()) {
            return $this->redirect(['/admin/videos/import-categories', 'preset' => $feed->feed_id]);
        }

        return $this->render('update', [
            'feed' => $feed,
        ]);
    }

    /**
     * Удаление фида
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        $feed = $this->findById($id);

        $name = $feed->name;

        if ($feed->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('videos', 'Feed "<b>{name}</b>" deleted', ['name' => $name]));
        }

        return $this->redirect(['index']);
    }

    /**
     * Удаление фида импорта
     *
     * @param int $id
     * @return CategoryImportFeed
     * @throws NotFoundHttpException
     */
    public function findById(int $id): CategoryImportFeed
    {
        $feed = CategoryImportFeed::findById($id);

        if ($feed === null) {
            throw new NotFoundHttpException('The requested feed does not exist.');
        }

        return $feed;
    }
}
