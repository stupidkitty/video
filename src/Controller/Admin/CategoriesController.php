<?php
namespace SK\VideoModule\Controller\Admin;

use Yii;
use yii\base\Event;
use yii\web\Request;
use yii\web\Controller;
use yii\base\DynamicModel;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use SK\VideoModule\Model\Category;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Form\Admin\CategoryForm;
use SK\VideoModule\EventSubscriber\CategorySubscriber;

/**
 * CategoriesController implements the CRUD actions for Category model.
 */
class CategoriesController extends Controller
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
                    'save-order' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id,['save-order'])) {
            $this->enableCsrfValidation = false;
        }

        Event::on(Category::class, Category::EVENT_BEFORE_INSERT, [CategorySubscriber::class, 'onCreate']);
        Event::on(Category::class, Category::EVENT_BEFORE_UPDATE, [CategorySubscriber::class, 'onUpdate']);
        Event::on(Category::class, Category::EVENT_BEFORE_DELETE, [CategorySubscriber::class, 'onDelete']);

        return parent::beforeAction($action);
    }

    /**
     * Lists all Category models.
     *
     * @return mixed
     */
    /*public function actionIndex()
    {
        $categories = Category::find()
            ->orderBy(['position' => SORT_ASC])
            ->all();

        $form = new CategoryForm();

        return $this->render('index', [
            'categories' => $categories,
            'form' => $form,
        ]);
    }*/

    /**
     * Displays a single Category model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $categories = Category::find()
            ->orderBy(['position' => SORT_ASC])
            ->all();


        $category = $this->findById($id);

        $clicksStats = (new \yii\db\Query)
            ->select('`date`, SUM(`clicks`) as `clicks`')
            ->from('videos_categories_stats')
            ->where(['category_id' => $id])
            ->groupBy('date')
            ->indexBy('date')
            ->all();

        $currentDate = new \DateTime('now', new \DateTimeZone('utc'));
        $stats = [];

        for ($i = 0; $i < 30; $i ++) {
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
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
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

                $this->redirect(['create']);
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
     * @param integer $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $category = $this->findById($id);

        $form = new CategoryForm;
        $form->setAttributes($category->getAttributes());

        if ($form->load($this->getRequest()->post()) && $form->isValid()) {
            $category->setAttributes($form->getAttributes());
            $category->generateSlug($form->slug);

            if ($category->save()) {
                Yii::$app->session->setFlash('success', 'Новые данные для категории сохранены');

                $this->redirect(['update', 'id' => $id]);
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
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $category = $this->findById($id);

        if ($category->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('videos', 'Категория "<b>{title}</b>" успешно удалена', ['title' => $category->getTitle()]));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('videos', 'Удалить категорию "<b>{title}</b>" не удалось', ['title' => $category->getTitle()]));
        }

        return $this->redirect(['create']);
    }

    /**
     * Сохраняет порядок сортировки категорий, установленный пользователем.
     * @return mixed
     */
    public function actionSaveOrder()
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
                '{{position}}' => new \yii\db\Expression("FIND_IN_SET(`category_id`, :id_list)"),
            ], [
                '!=', new \yii\db\Expression("FIND_IN_SET(`category_id`, :id_list)"), 0,
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
     * Поиск категории по ее идентификатору
     *
     * @param integer $id Идентификатор категории
     *
     * @return mixed
     *
     * @throw NotFoundHttpException Если категория не найдена.
     */
    protected function findById($id)
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
     * @return \yii\web\Request
     */
    protected function getRequest()
    {
        return Yii::$container->get(Request::class);
    }
}
