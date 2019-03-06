<?php
namespace SK\VideoModule\Controller\Admin;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Form\Admin\SettingsForm;

/**
 * SettingsController
 */
class SettingsController extends Controller
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
     * List base Settings and save it.
     * @return mixed
     */
    public function actionIndex()
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $form = new SettingsForm($settings->getAll('videos'));

        if ($form->load($this->getRequest()->post()) && $form->isValid()) {
            foreach ($form->getAttributes() as $name => $value) {
                $settings->set($name, $value, 'videos');
            }

            Yii::$app->session->setFlash('info', 'Настройки сохранены');
        }

        return $this->render('index', [
            'form' => $form,
        ]);
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
