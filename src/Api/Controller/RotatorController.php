<?php
namespace SK\VideoModule\Api\Controller;

use Yii;
use yii\web\Request;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use SK\VideoModule\Rotator\UserBehaviorHandler;
use SK\VideoModule\Rotator\UserBehaviorStatistic;

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
                ],
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
