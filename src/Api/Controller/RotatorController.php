<?php

namespace SK\VideoModule\Api\Controller;

use SK\VideoModule\Rotator\UserBehaviorHandler;
use SK\VideoModule\Rotator\UserBehaviorStatistic;
use SK\VideoModule\Service\Rotator;
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
    public function behaviors()
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
     * Processing statistics about the page viewed
     *
     * @param Request $request
     * @param UserBehaviorHandler $statsHandler
     * @return mixed
     */
    public function actionStats(Request $request, UserBehaviorHandler $statsHandler)
    {
        $stats = new UserBehaviorStatistic;
        $stats->categoriesClicked = $request->post('categoriesClicked', []);
        $stats->videosViewed = $request->post('videosViewed', []);
        $stats->videosClicked = $request->post('videosClicked', []);

        $statsHandler->handle($stats);

        return '';
    }

    /**
     * Reset zero crt thumbs action
     *
     * @return array[]|string
     */
    public function actionResetZeroCtr()
    {
        try {
            $rotator = new Rotator;
            $rotator->resetZeroCtr();
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
