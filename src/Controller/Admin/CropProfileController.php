<?php
namespace SK\VideoModule\Controller\Admin;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Model\CropProfile;
use SK\VideoModule\Form\Admin\CropProfileForm;

/**
 * CropProfile implements the CRUD actions for CropProfile model.
 */
class CropProfileController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * Lists all CropProfiles models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = CropProfile::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Gallery model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $crop = $this->findById($id);

        return $this->render('view', [
            'crop' => $crop,
        ]);
    }

    /**
     * Creates a new CropProfile model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $crop = new CropProfile;
        $form = new CropProfileForm;

        if ($form->load(Yii::$app->request->post()) && $form->isValid()) {
            $currentDatetime = gmdate('Y-m-d H:i:s');

            $crop->setAttributes($form->getAttributes());
            $crop->created_at = $currentDatetime;

            if ($crop->save()) {
                Yii::$app->session->addFlash('success', 'Новый профиль нарезки изображений добавлен');
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'crop' => $crop,
            'form' => $form,
        ]);
    }

    /**
     * Updates an existing CropProfile model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $crop = $this->findById($id);

        $form = new CropProfileForm;
        $form->setAttributes($crop->getAttributes());

        if ($form->load(Yii::$app->request->post()) && $form->isValid()) {
            $currentDatetime = gmdate('Y-m-d H:i:s');

            $crop->setAttributes($form->getAttributes());

            if ($crop->save()) {
                Yii::$app->session->addFlash('success', 'Crop profile updated: ' . $crop->getName());
            }

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'crop' => $crop,
            'form' => $form,
        ]);
    }

    /**
     * Deletes an existing CropProfile model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $crop = $this->findById($id);

        if ($crop->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('videos', 'Crop profile "{title}" deleted', ['title' => $crop->name]));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the CropProfile model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Gallery the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findById($id)
    {
        $crop = CropProfile::findOne($id);

        if (null === $crop) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $crop;
    }
}
