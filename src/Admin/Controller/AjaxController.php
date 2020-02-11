<?php
namespace SK\VideoModule\Admin\Controller;

use SK\VideoModule\Service\Rotator;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * AjaxController содержит различные аякс действия.
 */
class AjaxController extends Controller
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
                    'restart-zero-ctr' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Отключает csrf для аякса
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Перезапускает ротацию тестированных тумбы с 0 цтр.
     *
     * @return mixed
     */
    public function actionRestartZeroCtr()
    {
        try {
            $rotator = new Rotator;
            $rotator->resetZeroCtr();

            return $this->asJson([
                'message' => 'Ротация тестированных тумб с 0 ctr перезапущена',
            ]);
        } catch (\Throwable $e) {
            return $this->asJson([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ]);
        }
    }
}
