<?php

namespace SK\VideoModule\Admin\Controller;

use SK\VideoModule\Admin\Form\CategoryForm;
use SK\VideoModule\EventSubscriber\CategorySubscriber;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Service\Category as CategoryService;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\di\NotInstantiableException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii2tech\csvgrid\CsvGrid;

/**
 * CategoriesController implements the CRUD actions for Category model.
 */
class CategoriesController extends Controller
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
                    'delete' => ['post'],
                    'save-order' => ['post'],
                    'recalculate-videos' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['save-order', 'recalculate-videos'])) {
            $this->enableCsrfValidation = false;
        }

        Event::on(Category::class, Category::EVENT_BEFORE_INSERT, [CategorySubscriber::class, 'onCreate']);
        Event::on(Category::class, Category::EVENT_BEFORE_UPDATE, [CategorySubscriber::class, 'onUpdate']);
        Event::on(Category::class, Category::EVENT_BEFORE_DELETE, [CategorySubscriber::class, 'onDelete']);

        return parent::beforeAction($action);
    }

    /**
     * Displays a single Category model.
     *
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionView(int $id): string
    {
        $categories = Category::find()
            ->orderBy(['position' => SORT_ASC])
            ->all();

        $category = $this->findById($id);

        $clicksStats = (new Query)
            ->select('`date`, SUM(`clicks`) as `clicks`')
            ->from('videos_categories_stats')
            ->where(['category_id' => $id])
            ->groupBy('date')
            ->indexBy('date')
            ->all();

        $currentDate = new \DateTime('now', new \DateTimeZone('utc'));
        $stats = [];

        for ($i = 0; $i < 30; $i++) {
            $currentDay = $currentDate->format('Y-m-d');

            $clicks = (isset($clicksStats[$currentDay])) ? (int) $clicksStats[$currentDay]['clicks'] : 0;

            $stats[] = [
                'date' => $currentDay,
                'clicks' => $clicks,
            ];

            $currentDate->sub(new \DateInterval('P1D'));
        }
        $stats = array_reverse($stats);

        return $this->render('view', [
            'category' => $category,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    /**
     * Create new Category model.
     * If update is successful, the browser will be redirected to the 'create' page.
     *
     * @return Response|string
     */
    public function actionCreate()
    {
        $form = new CategoryForm;

        if ($form->load($this->getRequest()->post()) && $form->isValid()) {
            $category = new Category;
            $category->setAttributes($form->getAttributes());
            $category->generateSlug($form->slug);

            if ($category->save()) {
                Yii::$app->session->setFlash('success', Yii::t('videos', 'Категория "<b>{title}</b>" создана', ['title' => $category->title]));

                return $this->redirect(['create']);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка сохранения');
            }
        }

        $categories = Category::find()
            ->orderBy(['position' => SORT_ASC])
            ->all();

        return $this->render('create', [
            'form' => $form,
            'categories' => $categories,
        ]);
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     * @return Response|string
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        $category = $this->findById($id);

        $form = new CategoryForm;
        $form->setAttributes($category->getAttributes());

        if ($form->load($this->getRequest()->post()) && $form->isValid()) {
            $category->setAttributes($form->getAttributes());
            $category->generateSlug($form->slug);

            if ($category->save()) {
                Yii::$app->session->setFlash('success', 'Новые данные для категории сохранены');

                return $this->redirect(['update', 'id' => $id]);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка сохранения');
            }
        }

        $categories = Category::find()
            ->orderBy(['position' => SORT_ASC])
            ->all();

        return $this->render('update', [
            'category' => $category,
            'form' => $form,
            'categories' => $categories,
        ]);
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        $category = $this->findById($id);

        try {
            if ($category->delete()) {
                Yii::$app->session->setFlash('success', Yii::t('videos', 'Категория "<b>{title}</b>" успешно удалена', ['title' => $category->getTitle()]));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('videos', 'Удалить категорию "<b>{title}</b>" не удалось', ['title' => $category->getTitle()]));
            }
        } catch (StaleObjectException | Throwable $e) {
            Yii::$app->session->setFlash('error', Yii::t('videos', $e->getMessage()));
        }

        return $this->redirect(['create']);
    }

    /**
     * Сохраняет порядок сортировки категорий, установленный пользователем.
     *
     * @return Response
     * @throws InvalidConfigException
     */
    public function actionSaveOrder(): Response
    {
        // Валидация массива идентификаторов категорий.
        $validationModel = DynamicModel::validateData(['categories_ids' => $this->getRequest()->post('order')], [
            ['categories_ids', 'each', 'rule' => ['integer']],
            ['categories_ids', 'filter', 'filter' => 'array_filter'],
            ['categories_ids', 'required', 'message' => 'Categories not select'],
        ]);

        if ($validationModel->hasErrors()) {
            return $this->asJson([
                'error' => [
                    'code' => 1,
                    'message' => 'Validation fail',
                ],
            ]);
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            Category::updateAll([
                '{{position}}' => new Expression("FIND_IN_SET(`category_id`, :id_list)"),
            ], [
                '!=', new Expression("FIND_IN_SET(`category_id`, :id_list)"), 0,
            ], [
                ':id_list' => \implode(',', $validationModel->categories_ids),
            ]);

            $transaction->commit();

            return $this->asJson([
                'message' => 'Порядок сортировки категорий сохранен'
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();

            return $this->asJson([
                'error' => [
                    'code' => 2,
                    'message' => $e->getMessage(),
                ],
            ]);
        }
    }

    /**
     * Формирует csv для экспорта категорий.
     *
     * @return Response Csv file download
     * @throws InvalidConfigException
     */
    public function actionExport(): Response
    {
        $exporter = new CsvGrid([
            'csvFileConfig' => [
                'cellDelimiter' => '|',
                'enclosure' => '"',
            ],
            'dataProvider' => new ActiveDataProvider([
                'query' => Category::find(),
                'pagination' => [
                    'pageSize' => 50, // export batch size
                ],
            ]),
            'columns' => [
                'category_id',
                'attribute' => 'title',
                'meta_title',
                'meta_description',
                'h1',
                'description',
                'seotext',
                'param1',
                'param2',
                'param3',
            ],
        ]);

        return $exporter->export()->send('categories.csv');
    }

    /**
     * Пересчитывает активные видео в категориях.
     *
     * @return Response
     */
    public function actionRecalculateVideos(): Response
    {
        try {
            $categoryService = new CategoryService;
            $categoryService->countVideos();

            return $this->asJson([
                'message' => 'All active videos counted in categories',
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'error' => [
                    'code' => 422,
                    'message' => $e->getMessage(),
                ],
            ]);
        }
    }

    /**
     * Поиск категории по ее идентификатору
     *
     * @param int $id Идентификатор категории
     * @return Category
     * @throws NotFoundHttpException If category not found
     */
    protected function findById(int $id): Category
    {
        $category = Category::findOne($id);

        if (null === $category) {
            throw new NotFoundHttpException('The requested category does not exist.');
        }

        return $category;
    }

    /**
     * Get request class form DI container
     *
     * @return Request
     */
    protected function getRequest(): Request
    {
        try {
            return Yii::$container->get(Request::class);
        } catch (NotInstantiableException | InvalidConfigException $e) {
            return new Request();
        }
    }
}
