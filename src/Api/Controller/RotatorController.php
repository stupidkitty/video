<?php

namespace SK\VideoModule\Api\Controller;

use SK\VideoModule\Rotator\UserBehaviorHandler;
use SK\VideoModule\Rotator\UserBehaviorStatistic;
use SK\VideoModule\Rotator\ResetFields;
use yii\db\Exception;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Request;

/**
 * RotatorController
 */
class RotatorController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'stats' => ['post'],
                    'reset-zero-ctr' => ['post'],
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['stats'],
            ],
        ];
    }

    /**
     * Store page views and click statistic into db
     *
     * @param Request $request
     * @param UserBehaviorHandler $statsHandler
     * @return string
     * @throws Exception
     */
    public function actionStats(Request $request, UserBehaviorHandler $statsHandler): string
    {
        $stats = new UserBehaviorStatistic();
        $stats->categoriesClicked = $request->post('categoriesClicked', []);
        $stats->videosViewed = $request->post('videosViewed', []);
        $stats->videosClicked = $request->post('videosClicked', []);

        $statsHandler->handle($stats);

        return '';
    }

    /**
     * Reset zero ctr tested videos. New testing may be needed.
     *
     * @return array[]|string
     */
    public function actionResetZeroCtr(ResetFields $resetFields)
    {
        try {
            $resetFields->resetZeroCtr();
        } catch (\Throwable $e) {
            return [
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ];
        }

        return '';
    }
}
