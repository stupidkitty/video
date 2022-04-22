<?php

namespace SK\VideoModule\Admin\Controller;

use SK\VideoModule\Rotator\ResetFields;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * AjaxController содержит различные аякс действия.
 */
class AjaxController extends Controller
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
                    'restart-zero-ctr' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Отключает csrf для аякса
     *
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Перезапускает ротацию тестированных тумбы с 0 цтр.
     *
     * @param ResetFields $resetFields
     * @return Response
     */
    public function actionRestartZeroCtr(ResetFields $resetFields): Response
    {
        try {
            $resetFields->resetZeroCtr();

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
