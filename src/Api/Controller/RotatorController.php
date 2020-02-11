<?php
namespace SK\VideoModule\Api\Controller;

use SK\VideoModule\Rotator\UserBehaviorHandler;
use SK\VideoModule\Rotator\UserBehaviorStatistic;
use SK\VideoModule\Service\Rotator;
use Yii;
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
     * Gets info about auto postig. Max date post and count future posts.
     *
     * @return mixed
     */
    public function actionStats()
    {
        $request = $this->get(Request::class);
        $statsHandler = $this->get(UserBehaviorHandler::class);

        $stats = new UserBehaviorStatistic;
        $stats->categoriesClicked = $request->post('categoriesClicked', []);
        $stats->videosViewed = $request->post('videosViewed', []);
        $stats->videosClicked = $request->post('videosClicked', []);

        $statsHandler->handle($stats);

        return '';
    }

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

    /**
     * Короткий метод для получения данных с контейнера DI
     *
     * @param string $item
     * @return void
     */
    protected function get($item)
    {
        return Yii::$container->get($item);
    }
}
